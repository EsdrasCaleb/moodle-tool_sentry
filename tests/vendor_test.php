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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/sentry/vendor/autoload.php');

/**
 * Tests for the bundled Sentry SDK.
 *
 * @package    tool_sentry
 * @category   test
 * @copyright  2026 Kelson da Costa Medeiros
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversNothing
 */
final class vendor_test extends \advanced_testcase {
    /**
     * The bundled SDK loads on the PHP versions of the CI (Composer platform check) and resolves its options.
     */
    public function test_sdk_options(): void {
        $options = new \Sentry\Options([
            'dsn' => 'https://public@example.invalid/1',
            'environment' => 'phpunit',
            'max_request_body_size' => 'never',
        ]);
        $this->assertSame('phpunit', $options->getEnvironment());
        $this->assertSame('never', $options->getMaxRequestBodySize());
        $this->assertNotNull($options->getDsn());
    }
}
