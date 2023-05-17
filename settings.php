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
 * @package    tool
 * @subpackage sentry
 * @author     Esdras Caleb <esdrascaleb@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;

if (is_siteadmin()) {
    if (!$ADMIN->locate('tool_sentry')) {
        $page = new admin_settingpage('sentryconfig', get_string('pluginsettigs', 'tool_sentry'));
        $page->add(new admin_setting_configtext('tool_sentry/dns', get_string('dns', 'tool_sentry'),
            '', 'https://USERCODE@CLIENTCODE.ingest.sentry.io/CLIENTCODE'));
        $ADMIN->add('tools', $page);

    }
}
