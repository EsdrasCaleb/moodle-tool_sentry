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
 * Starts Sentry before Moodle. Set it in php.ini: auto_prepend_file=/path/to/moodle/admin/tool/sentry/prepend.php
 *
 * PHP runs this file before every script (web and CLI), before config.php, so it cannot check MOODLE_INTERNAL.
 * Without the SENTRY_DSN environment variable it does nothing and the plugin keeps its usual behaviour.
 *
 * @package    tool_sentry
 * @copyright  2026 Kelson da Costa Medeiros
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.Files.MoodleInternal.MoodleInternalGlobalState

if (getenv('SENTRY_DSN') === false || getenv('SENTRY_DSN') === '') {
    return;
}

// Opened directly by URL: nothing to do.
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    return;
}

require_once(__DIR__ . '/classes/local/prepend.php');
\tool_sentry\local\prepend::start();
