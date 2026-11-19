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

use tool_sentry\local\prepend;

/**
 * Tests for the choice between the prepend mode and the plugin mode.
 *
 * @package    tool_sentry
 * @category   test
 * @copyright  2026 Kelson da Costa Medeiros
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tool_sentry\helper
 */
final class helper_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        prepend::set_active_for_testing(false);
        $this->set_initialized(false);
        \Sentry\SentrySdk::init();
    }

    protected function tearDown(): void {
        prepend::set_active_for_testing(false);
        $this->set_initialized(false);
        \Sentry\SentrySdk::init();
        parent::tearDown();
    }

    /**
     * Changes helper::$initialized.
     *
     * @param bool $value New value.
     */
    private function set_initialized(bool $value): void {
        $property = new \ReflectionProperty(helper::class, 'initialized');
        $property->setAccessible(true);
        $property->setValue(null, $value);
    }

    /**
     * Configures the plugin mode in the settings.
     */
    private function configure_plugin(): void {
        set_config('activate', 1, 'tool_sentry');
        set_config('dsn', 'https://public@example.invalid/1', 'tool_sentry');
    }

    /**
     * Without settings and without prepend, Sentry is off.
     */
    public function test_mode_none(): void {
        set_config('activate', 0, 'tool_sentry');
        $this->assertSame(helper::MODE_NONE, helper::get_mode());
        set_config('activate', 1, 'tool_sentry');
        set_config('dsn', '', 'tool_sentry');
        $this->assertSame(helper::MODE_NONE, helper::get_mode());
    }

    /**
     * Without prepend, the current flow (settings of the plugin) is used.
     */
    public function test_mode_plugin(): void {
        $this->configure_plugin();
        $this->assertSame(helper::MODE_PLUGIN, helper::get_mode());
    }

    /**
     * Prepend mode wins over the settings of the plugin.
     */
    public function test_mode_prepend(): void {
        $this->configure_plugin();
        prepend::set_active_for_testing(true);
        $this->assertSame(helper::MODE_PREPEND, helper::get_mode());

        set_config('activate', 0, 'tool_sentry');
        $this->assertSame(helper::MODE_PREPEND, helper::get_mode());
    }

    /**
     * With prepend active, init() does not start the SDK again.
     */
    public function test_init_prepend_does_not_start_again(): void {
        $this->configure_plugin();
        prepend::set_active_for_testing(true);

        helper::init();

        $this->assertNull(\Sentry\SentrySdk::getCurrentHub()->getClient());
    }

    /**
     * Without prepend and without settings, init() does nothing.
     */
    public function test_init_none(): void {
        set_config('activate', 0, 'tool_sentry');

        helper::init();

        $this->assertNull(\Sentry\SentrySdk::getCurrentHub()->getClient());
    }

    /**
     * Without prepend, init() keeps the current flow and starts the SDK with the settings of the plugin.
     */
    public function test_init_plugin_starts_sdk(): void {
        $this->configure_plugin();
        set_config('environment', 'phpunit', 'tool_sentry');

        try {
            helper::init();
            $client = \Sentry\SentrySdk::getCurrentHub()->getClient();
            $this->assertNotNull($client);
            $this->assertSame('phpunit', $client->getOptions()->getEnvironment());
        } finally {
            // The handler registered by init() would send the warnings of the next tests to Sentry.
            restore_error_handler();
        }
    }
}
