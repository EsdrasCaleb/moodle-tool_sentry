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
 * @author    Esdras Caleb
 * @copyright  2023 Esdras Caleb
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Initialize sentry
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function init(?\core\event\base $event = null) {
        $config = get_config('tool_sentry');
        if ($config->activate) {
            unset($config->activate);
            unset($config->dns);
            unset($config->version);
            if ($config->ignore_exceptions == "") {
                unset($config->ignore_exceptions);
            }
            if ($config->ignore_transactions == "") {
                unset($config->ignore_transactions);
            }
            if ($config->in_app_exclude == "") {
                unset($config->in_app_exclude);
            }
            if ($config->in_app_include == "") {
                unset($config->in_app_include);
            }
            $config->enable_tracing = boolval($config->enable_tracing);
            $config->attach_stacktrace = boolval($config->attach_stacktrace);
            $config->send_default_pii = boolval($config->send_default_pii);
            $config = (array) $config;

            foreach ($config as $name => $value) {
                if (is_numeric($value)) {
                    if (strpos($value, '.')) {
                        $config[$name] = floatval($value);
                    }
                    else {
                        $config[$name] = intval($value);
                    }
                }
            }

            \Sentry\init($config);
        }
    }

    /**
     * Get erros and send
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function geterros(?\core\event\base $event = null) {
        $config = get_config('tool_sentry');
        if ($config->activate) {
            \Sentry\captureLastError();
        }
    }
}
