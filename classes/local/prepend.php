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

namespace tool_sentry\local;

/**
 * Starts Sentry before Moodle, from the PHP auto_prepend_file directive.
 *
 * This class is loaded by prepend.php before config.php, so it must not use any Moodle API
 * (no $CFG, no database, no get_config()). Its configuration comes only from environment variables:
 *
 *  - SENTRY_DSN: project DSN. Without it nothing happens and the plugin keeps working as before.
 *  - SENTRY_ENVIRONMENT: environment name (default "production").
 *  - SENTRY_RELEASE: release name (for example the image tag).
 *  - SENTRY_SAMPLE_RATE: 0.0 to 1.0 (default 1.0).
 *  - SENTRY_IGNORE_EXCEPTIONS: comma separated class names; replaces the default list.
 *  - SENTRY_SEND_USER_ID: "true" sends only the numeric id of the Moodle user (default false).
 *
 * @package    tool_sentry
 * @copyright  2026 Kelson da Costa Medeiros
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class prepend {
    /** @var string Response header with the id of the captured event (useful for error pages in the web server). */
    public const HEADER = 'X-Sentry-Event-Id';

    /** @var string[] Environment variables read by this class. */
    public const ENV_VARIABLES = [
        'SENTRY_DSN', 'SENTRY_ENVIRONMENT', 'SENTRY_RELEASE', 'SENTRY_SAMPLE_RATE', 'SENTRY_IGNORE_EXCEPTIONS',
        'SENTRY_SEND_USER_ID',
    ];

    /** @var string[] Exceptions of the normal Moodle flow (login, permission, parameter) that are only noise. */
    public const DEFAULT_IGNORE_EXCEPTIONS = [
        'require_login_exception',
        'require_login_session_timeout_exception',
        'required_capability_exception',
        'invalid_parameter_exception',
    ];

    /** @var string[] Request parameters whose values must never leave the server. */
    public const SENSITIVE_PARAMETERS = [
        'token', 'wstoken', 'password', 'senha', 'sesskey', 'key', 'secret', 'code', 'ticket',
    ];

    /** @var bool Whether this class started Sentry in the current request. */
    private static $active = false;

    /** @var bool Whether the handlers were already chained after lib/setup.php. */
    private static $chained = false;

    /**
     * Path of the file that must be set in auto_prepend_file.
     *
     * @return string
     */
    public static function prepend_file(): string {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'prepend.php';
    }

    /**
     * Reads an environment variable.
     *
     * @param string $name Variable name.
     * @param string $default Value returned when the variable is missing or empty.
     * @return string
     */
    public static function env(string $name, string $default = ''): string {
        $value = getenv($name);
        return ($value === false || $value === '') ? $default : $value;
    }

    /**
     * Whether the site is configured to start Sentry from the prepend file.
     *
     * Both conditions are required: auto_prepend_file pointing to this plugin's prepend.php and SENTRY_DSN set.
     *
     * @param string|null $prependfile Value of auto_prepend_file (read from php.ini when null).
     * @param string|null $dsn DSN (read from SENTRY_DSN when null).
     * @return bool
     */
    public static function is_configured(?string $prependfile = null, ?string $dsn = null): bool {
        $prependfile = $prependfile ?? (string) ini_get('auto_prepend_file');
        $dsn = $dsn ?? self::env('SENTRY_DSN');
        if ($dsn === '' || $prependfile === '') {
            return false;
        }
        $expected = realpath(self::prepend_file());
        $actual = realpath($prependfile);
        return $expected !== false && $actual !== false && $expected === $actual;
    }

    /**
     * Whether Sentry was started by the prepend file in this request.
     *
     * When true, the plugin must not start the SDK again.
     *
     * @return bool
     */
    public static function is_active(): bool {
        return self::$active;
    }

    /**
     * Changes the active flag. Only for PHPUnit tests.
     *
     * @param bool $active New value.
     * @return void
     */
    public static function set_active_for_testing(bool $active): void {
        if (!defined('PHPUNIT_TEST') || !PHPUNIT_TEST) {
            throw new \coding_exception('set_active_for_testing() can only be used in PHPUnit tests');
        }
        self::$active = $active;
        self::$chained = false;
    }

    /**
     * Sentry options built from the environment variables.
     *
     * @param array $env Environment variables (name => value). Missing names use the defaults.
     * @return array
     */
    public static function options(array $env): array {
        $get = function (string $name, string $default = '') use ($env): string {
            $value = isset($env[$name]) ? (string) $env[$name] : '';
            return $value === '' ? $default : $value;
        };

        $ignore = $get('SENTRY_IGNORE_EXCEPTIONS');
        $ignorelist = $ignore === ''
            ? self::DEFAULT_IGNORE_EXCEPTIONS
            : array_values(array_filter(array_map('trim', explode(',', $ignore))));

        // Plugin in <root>/public/admin/tool/sentry: Moodle libraries in public/lib/vendor, Composer in <root>/vendor.
        $pluginroot = dirname(__DIR__, 2);
        $moodleroot = dirname($pluginroot, 3);

        return [
            'dsn' => $get('SENTRY_DSN'),
            'environment' => $get('SENTRY_ENVIRONMENT', 'production'),
            'release' => $get('SENTRY_RELEASE') ?: null,
            'sample_rate' => (float) $get('SENTRY_SAMPLE_RATE', '1.0'),
            // Fixed: the SDK default is the current error_reporting(), which Moodle sets to 0 when debugging is off,
            // and then not even fatal errors would be sent. After lib/setup.php, warnings follow Moodle's
            // error_reporting() in the handler chained by chain_handlers().
            'error_types' => E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE | E_USER_NOTICE),
            'send_default_pii' => false,
            // POST bodies may carry passwords (login, password change): never send them.
            'max_request_body_size' => 'never',
            'ignore_exceptions' => $ignorelist,
            'in_app_exclude' => [
                $pluginroot . '/vendor',
                $moodleroot . '/lib/vendor',
                dirname($moodleroot) . '/vendor',
            ],
            'before_send' => [self::class, 'before_send'],
        ];
    }

    /**
     * Starts Sentry. Called by prepend.php, before Moodle.
     *
     * @return bool True when Sentry was started.
     */
    public static function start(): bool {
        if (self::$active || self::env('SENTRY_DSN') === '') {
            return false;
        }
        $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (!is_readable($autoload)) {
            return false;
        }
        require_once($autoload);

        // Read name by name: getenv() without arguments misses variables passed by the web server (fastcgi_param, SetEnv).
        $env = [];
        foreach (self::ENV_VARIABLES as $name) {
            $env[$name] = self::env($name);
        }
        \Sentry\init(self::options($env));
        self::$active = true;

        \Sentry\configureScope(static function (\Sentry\State\Scope $scope): void {
            $scope->setTag('sapi', PHP_SAPI);
            $requestid = $_SERVER['HTTP_X_REQUEST_ID'] ?? '';
            if (is_string($requestid) && preg_match('/^[A-Za-z0-9._-]{1,64}$/', $requestid)) {
                $scope->setTag('request_id', $requestid);
            }
        });

        // Fatal errors are captured by the SDK itself on shutdown. This runs after it and, if the response has not
        // started yet, exposes the event id to the web server error page.
        register_shutdown_function(static function (): void {
            $last = error_get_last();
            $fatal = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR;
            if ($last === null || !($last['type'] & $fatal) || headers_sent()) {
                return;
            }
            $id = \Sentry\SentrySdk::getCurrentHub()->getLastEventId();
            if ($id !== null) {
                header(self::HEADER . ': ' . $id);
            }
        });
        return true;
    }

    /**
     * Moodle component of a file path (mod_assign, block_x, local_y...), or "core".
     *
     * @param string $file Absolute file path.
     * @return string
     */
    public static function component(string $file): string {
        $file = str_replace('\\', '/', $file);
        $patterns = [
            '#/mod/([^/]+)/#' => 'mod_',
            '#/blocks/([^/]+)/#' => 'block_',
            '#/local/([^/]+)/#' => 'local_',
            '#/admin/tool/([^/]+)/#' => 'tool_',
            '#/availability/condition/([^/]+)/#' => 'availability_',
            '#/auth/([^/]+)/#' => 'auth_',
            '#/enrol/([^/]+)/#' => 'enrol_',
            '#/report/([^/]+)/#' => 'report_',
            '#/theme/([^/]+)/#' => 'theme_',
            '#/question/type/([^/]+)/#' => 'qtype_',
            '#/filter/([^/]+)/#' => 'filter_',
            '#/course/format/([^/]+)/#' => 'format_',
            '#/grade/report/([^/]+)/#' => 'gradereport_',
        ];
        foreach ($patterns as $pattern => $prefix) {
            if (preg_match($pattern, $file, $m)) {
                return $prefix . $m[1];
            }
        }
        return 'core';
    }

    /**
     * Replaces with [Filtered] the values of sensitive parameters of a query string.
     *
     * @param string $query Query string.
     * @return string
     */
    public static function filter_query(string $query): string {
        if ($query === '') {
            return $query;
        }
        parse_str($query, $params);
        foreach ($params as $name => $value) {
            if (in_array(strtolower((string) $name), self::SENSITIVE_PARAMETERS, true)) {
                $params[$name] = '[Filtered]';
            }
        }
        return http_build_query($params, '', '&');
    }

    /**
     * Adds the context of the running Moodle task (cron/adhoc).
     *
     * Only uses classes already loaded: if the task manager was not loaded, no task is running.
     *
     * @param \Sentry\Event $event Event.
     * @return void
     */
    private static function task_context(\Sentry\Event $event): void {
        if (
            !defined('CLI_SCRIPT') || !CLI_SCRIPT || !class_exists('\\core\\task\\manager', false)
            || !method_exists('\\core\\task\\manager', 'get_running_task')
        ) {
            return;
        }
        $task = \core\task\manager::get_running_task();
        if ($task === null) {
            return;
        }
        $adhoc = $task instanceof \core\task\adhoc_task;
        $event->setTag('task_type', $adhoc ? 'adhoc' : 'scheduled');
        $event->setTag('task_class', get_class($task));
        $event->setTag('task_component', (string) $task->get_component());
        if ($adhoc && $task->get_id()) {
            $event->setTag('task_id', (string) $task->get_id());
        }
        if (class_exists('\\core\\task\\logmanager', false) && method_exists('\\core\\task\\logmanager', 'get_current_logid')) {
            $logid = \core\task\logmanager::get_current_logid();
            if ($logid) {
                $event->setTag('task_logid', (string) $logid);
            }
        }
    }

    /**
     * Enriches and cleans each event before it is sent.
     *
     * @param \Sentry\Event $event Event.
     * @param \Sentry\EventHint|null $hint Hint with the original exception.
     * @return \Sentry\Event|null
     */
    public static function before_send(\Sentry\Event $event, ?\Sentry\EventHint $hint = null): ?\Sentry\Event {
        $exception = $hint ? $hint->exception : null;
        if ($exception instanceof \Throwable && !isset($event->getTags()['moodle_component'])) {
            $event->setTag('moodle_component', self::component($exception->getFile()));
        }
        if ($exception instanceof \moodle_exception) {
            $event->setTag('moodle_errorcode', (string) $exception->errorcode);
            $event->setTag('moodle_module', (string) $exception->module);
        }

        self::task_context($event);

        if (
            self::env('SENTRY_SEND_USER_ID') === 'true' && isset($GLOBALS['USER']->id)
            && (int) $GLOBALS['USER']->id > 0
        ) {
            $event->setUser(\Sentry\UserDataBag::createFromUserIdentifier((string) (int) $GLOBALS['USER']->id));
        }

        $request = $event->getRequest();
        if ($request) {
            if (!empty($request['query_string'])) {
                $request['query_string'] = self::filter_query((string) $request['query_string']);
            }
            if (!empty($request['url']) && strpos((string) $request['url'], '?') !== false) {
                [$base, $query] = explode('?', (string) $request['url'], 2);
                $request['url'] = $base . '?' . self::filter_query($query);
            }
            $event->setRequest($request);
        }
        return $event;
    }

    /**
     * Captures an exception and exposes the event id in the X-Sentry-Event-Id header.
     *
     * Can be used in config.php to capture exceptions thrown inside lib/setup.php (for example, database down).
     *
     * @param \Throwable $e Exception.
     * @return string|null Event id.
     */
    public static function capture(\Throwable $e): ?string {
        if (!self::$active) {
            return null;
        }
        $id = \Sentry\captureException($e);
        if ($id === null) {
            return null;
        }
        $id = (string) $id;
        if (!headers_sent()) {
            header(self::HEADER . ': ' . $id);
        }
        return $id;
    }

    /**
     * Chains the handlers after lib/setup.php, which replaces Sentry's handlers with Moodle's.
     *
     * Uncaught exceptions are captured and passed to Moodle's handler, which builds the error page.
     * PHP errors (warnings) are captured following Moodle's error_reporting() ($CFG->debug) and passed on.
     *
     * @return bool True when the handlers were chained now.
     */
    public static function chain_handlers(): bool {
        if (!self::$active || self::$chained) {
            return false;
        }
        self::$chained = true;

        $previous = set_exception_handler(null);
        set_exception_handler(static function (\Throwable $e) use ($previous): void {
            self::capture($e);
            if (is_callable($previous)) {
                $previous($e);
                return;
            }
            throw $e;
        });

        $previouserror = set_error_handler(null);
        set_error_handler(static function (
            int $errno,
            string $errstr,
            string $errfile = '',
            int $errline = 0
        ) use ($previouserror): bool {
            if (error_reporting() & $errno) {
                $error = new \ErrorException($errstr, 0, $errno, $errfile, $errline);
                \Sentry\withScope(static function (\Sentry\State\Scope $scope) use ($error, $errfile): void {
                    $scope->setTag('moodle_component', self::component($errfile));
                    \Sentry\captureException($error);
                });
            }
            if (is_callable($previouserror)) {
                return (bool) $previouserror($errno, $errstr, $errfile, $errline);
            }
            return false;
        });
        return true;
    }
}
