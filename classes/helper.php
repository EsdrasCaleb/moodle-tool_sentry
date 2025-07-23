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

require_once($CFG->dirroot.'/admin/tool/sentry/vendor/autoload.php');

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
    public static function get_clean_config($config): ?array {
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

        $config->enable_tracing = !empty($config->enable_tracing);
        $config->attach_stacktrace = !empty($config->attach_stacktrace);
        $config->send_default_pii = !empty($config->send_default_pii);

        $configArray = (array) $config;

        foreach ($configArray as $name => $value) {
            if (is_numeric($value)) {
                if (strpos($value, '.') !== false) {
                    $configArray[$name] = floatval($value);
                } else {
                    $configArray[$name] = intval($value);
                }
            }
        }

        return $configArray;
    }

    /**
     * Initialize sentry.
     *
     * @param \core\event\base|null $event The event.
     * @return void
     */
    public static function init(?\core\event\base $event = null): void {
        $config = get_config('tool_sentry');
        if (isset($config->activate) && $config->activate) {
            if (!self::$initialized) {
                self::$initialized = true;
                self::inject_sentry_js();
                \Sentry\init(self::get_clean_config($config));
            }
        }
    }

    /**
     * Capture last PHP error (if any).
     *
     * @param \core\event\base|null $event The event.
     * @return void
     */
    public static function geterros(?\core\event\base $event = null): void {
        $config = get_config('tool_sentry');
        if (isset($config->activate) && $config->activate) {
            \Sentry\captureLastError();
        }
    }

    /**
     * Injects Sentry JS loader and init code into the page.
     *
     * @return void
     */
    private static function inject_sentry_js(): void {
        $config = get_config('tool_sentry');

        if (empty($config->activate) || empty($config->javascriptloader)) {
            return;
        }

        $javascriptloader = $config->javascriptloader;
        $config = self::get_clean_config($config);
        if ($config === null) {
            return;
        }

        $config_json = json_encode($config);

        echo <<<JS
<script src="{$javascriptloader}" crossorigin="anonymous"></script>
<script>
  Sentry.init({$config_json});
</script>
JS;
    }
}
