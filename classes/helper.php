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
        $patterns = [
            '#/mod/([^/]+)/#'                   => 'mod_',
            '#/blocks/([^/]+)/#'                => 'block_',
            '#/local/([^/]+)/#'                 => 'local_',
            '#/admin/tool/([^/]+)/#'            => 'tool_',
            '#/availability/condition/([^/]+)/#' => 'availability_',
            '#/auth/([^/]+)/#'                  => 'auth_',
            '#/enrol/([^/]+)/#'                 => 'enrol_',
            '#/report/([^/]+)/#'                => 'report_',
            '#/theme/([^/]+)/#'                 => 'theme_',
            '#/question/type/([^/]+)/#'         => 'qtype_',
            '#/filter/([^/]+)/#'                => 'filter_',
            '#/course/format/([^/]+)/#'         => 'format_',
            '#/grade/report/([^/]+)/#'          => 'gradereport_',
        ];
        foreach ($patterns as $pattern => $prefix) {
            if (preg_match($pattern, $file, $m)) {
                return $prefix . $m[1];
            }
        }
        return 'core';
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
        $config = get_config('tool_sentry');
        $sentryconfig = self::get_clean_config($config);

        if ($sentryconfig) {
            if (!self::$initialized) {
                self::$initialized = true;
                self::inject_sentry_js();
                \Sentry\init($sentryconfig);

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
