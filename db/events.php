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
 * Evetns to start sentry sdk and get errors
 *
 * @package   tool_sentry
 * @author    Esdras Caleb
 * @copyright  2023 Esdras Caleb
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = array();

$observers[] = array(
    'eventname' => 'core\event\base',
    'callback' => '\tool_sentry\helper::init',
    'internal' => true,
    'priority'    => 9999,
);

$observers[] = array(
    'eventname' => 'core\event\base',
    'callback' => '\tool_sentry\helper::geterros',
    'internal' => true,
    'priority'    => 0,
);
