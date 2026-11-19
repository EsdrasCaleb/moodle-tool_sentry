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

namespace tool_sentry;

/**
 * Hook callbacks.
 *
 * @package    tool_sentry
 * @copyright  2026 Kelson da Costa Medeiros
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Chains Sentry's handlers again after lib/setup.php replaced them with Moodle's (prepend mode only).
     *
     * @param \core\hook\after_config $hook Hook.
     * @return void
     */
    public static function after_config(\core\hook\after_config $hook): void {
        \tool_sentry\local\prepend::chain_handlers();
    }
}
