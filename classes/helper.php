<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version information
 *
 * @package    tool_sentry
 * @author     Esdras Caleb <esdrascaleb@gmail.com>
 * @copyright  2023 Esdras Caleb
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_sentry;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/admin/tool/sentry/vendor/autoload.php');

/**
 * Class helper to provide functions to events
 *
 * @package    tool_sentry
 * @author     Esdras Caleb
 * @copyright  2023 Esdras Caleb
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /** @var string Sentry started before Moodle by prepend.php (auto_prepend_file + SENTRY_DSN). */
    public const MODE_PREPEND = 'prepend';

    /** @var string Sentry started by this plugin, with the settings saved in the administration pages. */
    public const MODE_PLUGIN = 'plugin';

    /** @var string Sentry disabled. */
    public const MODE_NONE = 'none';

    /** @var bool Whether Sentry has already been initialized. */
    private static $initialized = false;

    /**
     * Cleans and converts Sentry config object into array with correct types.
     *
     * @param \stdClass $config Raw plugin config.
     * @return array|null Clean config array or null if invalid.
     */
    private static function get_clean_config($config): ?array {
        if (empty($config->activate) || empty($config->dsn)) {
            return null;
        }

        unset($config->activate);
        unset($config->version);
        unset($config->javascriptloader);

        foreach (['ignore_exceptions', 'ignore_transactions', 'in_app_exclude', 'in_app_include'] as $key) {
            if (isset($config->$key) && $config->$key === "") {
                unset($config->$key);
            }
        }

        $config->enable_tracing = !empty($config->enable_tracing ?? '');
        $config->attach_stacktrace = !empty($config->attach_stacktrace ?? '');
        $config->send_default_pii = !empty($config->send_default_pii ?? '');

        $configarray = (array) $config;

        foreach ($configarray as $name => $value) {
            if (is_numeric($value) && $name !== 'release') {
                if (strpos($value, '.') !== false) {
                    $configarray[$name] = floatval($value);
                } else {
                    $configarray[$name] = intval($value);
                }
            }
        }

        return $configarray;
    }

    /**
     * Detects the Moodle component name from a file path.
     *
     * Examples:
     *   /var/www/moodle/mod/assign/lib.php        => mod_assign
     *   /var/www/moodle/blocks/myblock/block.php  => block_myblock
     *   /var/www/moodle/local/suap/lib.php        => local_suap
     *
     * @param string $file Absolute file path.
     * @return string Moodle component name or 'core'.
     */
    private static function get_moodle_component(string $file): string {
        return \tool_sentry\local\prepend::component($file);
    }

    /**
     * Detects whether the current execution is a Moodle task (cron/adhoc)
     * and, if so, enriches the Sentry scope with task-specific context:
     *
     *  - A synthetic URL of the form:
     *      cron://hostname/task/component/classname?id=123&logid=456
     *    so that Sentry has a non-empty, searchable "url" field.
     *  - Tags: task_type (scheduled|adhoc), task_class, task_component,
     *          task_id (adhoc only), task_logid (when available).
     *
     * Safe to call even outside CLI; if no task is running it does nothing.
     *
     * @param \Sentry\State\Scope $scope Active Sentry scope.
     * @return void
     */
    private static function set_task_context(\Sentry\State\Scope $scope): void {
        global $CFG;

        // Only meaningful during CLI/cron execution.
        if (!defined('CLI_SCRIPT') || !CLI_SCRIPT) {
            return;
        }

        // Try to get the currently running task from Moodle's task manager.
        // get_running_task() is available since Moodle 3.7.
        if (!method_exists('\core\task\manager', 'get_running_task')) {
            return;
        }

        $task = \core\task\manager::get_running_task();
        if ($task === null) {
            return;
        }

        $classname  = get_class($task);
        $shortclass = ltrim(strrchr($classname, '\\'), '\\') ?: $classname;
        $component  = $task->get_component();
        $hostname   = gethostname() ?: 'localhost';
        $isadhoc    = ($task instanceof \core\task\adhoc_task);
        $tasktype   = $isadhoc ? 'adhoc' : 'scheduled';

        // Build a synthetic URL that is human-readable and filterable in Sentry.
        // Format: cron://hostname/task/component/ShortClassName.
        $url = 'cron://' . $hostname . '/task/' . $component . '/' . $shortclass;

        $queryparams = [];

        // Adhoc tasks have a database id (\core\task\adhoc_task::get_id()).
        if ($isadhoc && method_exists($task, 'get_id') && $task->get_id()) {
            $taskid = $task->get_id();
            $queryparams[] = 'id=' . $taskid;
            $scope->setTag('task_id', (string) $taskid);
        }

        // The cron log id is available via \core\task\logmanager since Moodle 3.7.
        if (!empty($CFG->task_logmode) && defined('PHPUNIT_TEST') === false) {
            if (
                class_exists('\core\task\logmanager') &&
                method_exists('\core\task\logmanager', 'get_current_logid')
            ) {
                $logid = \core\task\logmanager::get_current_logid();
                if ($logid) {
                    $queryparams[] = 'logid=' . $logid;
                    $scope->setTag('task_logid', (string) $logid);
                }
            }
        }

        if ($queryparams) {
            $url .= '?' . implode('&', $queryparams);
        }

        // Set the synthetic URL so Sentry shows it in the "Request" section.
        $scope->setTag('url', $url);
        $scope->setTag('task_type', $tasktype);
        $scope->setTag('task_class', $classname);
        $scope->setTag('task_component', $component);

        // Also populate the request context so the URL appears in Sentry UI.
        \Sentry\configureScope(function (\Sentry\State\Scope $s) use ($url): void {
            $s->setContext('task', ['url' => $url]);
        });
    }

    /**
     * How Sentry must be started in this request.
     *
     * The prepend mode wins: when prepend.php already started the SDK, the plugin must not start it again.
     *
     * @return string One of the MODE_* constants.
     */
    public static function get_mode(): string {
        if (\tool_sentry\local\prepend::is_active()) {
            return self::MODE_PREPEND;
        }
        return self::get_clean_config(get_config('tool_sentry')) ? self::MODE_PLUGIN : self::MODE_NONE;
    }

    /**
     * Initialize sentry.
     *
     * Registers a custom PHP error handler so that Sentry receives the real
     * file and line where each error occurred, instead of always pointing to
     * this helper class.
     *
     * @param \core\event\base|null $event The event.
     * @return void
     */
    public static function init(?\core\event\base $event = null): void {
        if (self::get_mode() === self::MODE_PREPEND) {
            // The PHP SDK was already started by prepend.php, with the options of the environment variables.
            // Only the browser loader, when configured in the settings, is still added here.
            if (!self::$initialized) {
                self::$initialized = true;
                self::inject_sentry_js();
            }
            return;
        }

        $config = get_config('tool_sentry');
        $sentryconfig = self::get_clean_config($config);

        if ($sentryconfig) {
            if (!self::$initialized) {
                self::$initialized = true;
                self::inject_sentry_js();
                \Sentry\init($sentryconfig);

                // Enrich scope with task context when running as cron/adhoc.
                \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                    self::set_task_context($scope);
                });

                // Register a custom error handler so Sentry captures the real
                // origin of each error, not the location of this helper.
                set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
                    // Respect the @ error-suppression operator.
                    if (!(error_reporting() & $errno)) {
                        return false;
                    }
                    $exception = new \ErrorException($errstr, 0, $errno, $errfile, $errline);
                    \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($exception, $errfile): void {
                        $scope->setTag('moodle_component', self::get_moodle_component($errfile));
                        \Sentry\captureException($exception);
                    });
                    // Return false so Moodle's own error handler also runs.
                    return false;
                });
            }
        }
    }

    /**
     * Capture last PHP error (if any).
     *
     * This is a shutdown fallback for fatal errors (E_ERROR, E_PARSE, etc.)
     * that cannot be caught by set_error_handler. Non-fatal errors are already
     * handled by the handler registered in init().
     *
     * @param \core\event\base|null $event The event.
     * @return void
     */
    public static function geterros(?\core\event\base $event = null): void {
        if (self::get_mode() === self::MODE_PREPEND) {
            // The SDK started by prepend.php already captures fatal errors on shutdown.
            return;
        }
        $config = get_config('tool_sentry');
        if (isset($config->activate) && $config->activate) {
            $last = error_get_last();
            $fataltypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
            if ($last && in_array($last['type'], $fataltypes, true)) {
                $exception = new \ErrorException(
                    $last['message'],
                    0,
                    $last['type'],
                    $last['file'],
                    $last['line']
                );
                \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($exception, $last): void {
                    $scope->setTag('moodle_component', self::get_moodle_component($last['file']));
                    \Sentry\captureException($exception);
                });
            }
        }
    }

    /**
     * Injects Sentry JS loader and init code into the page.
     *
     * @return void
     */
    private static function inject_sentry_js(): void {
        global $PAGE;

        $config = get_config('tool_sentry');
        if (empty($config->activate) || empty($config->javascriptloader)) {
            return;
        }

        $javascriptloader = $config->javascriptloader;
        $config = self::get_clean_config($config);
        if ($config === null) {
            return;
        }

        $configjson = json_encode($config);
        $code = "
        (function() {
            const script = document.createElement('script');
            script.src = '$javascriptloader';
            script.crossOrigin = 'anonymous';
            script.onload = function() {
                Sentry.init($configjson);
            };
            document.head.appendChild(script);
        })();";

        $PAGE->requires->js_init_code($code);
    }
}
