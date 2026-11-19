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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/sentry/vendor/autoload.php');

/**
 * Tests for the prepend mode (Sentry started by auto_prepend_file).
 *
 * @package    tool_sentry
 * @category   test
 * @copyright  2026 Kelson da Costa Medeiros
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tool_sentry\local\prepend
 */
final class prepend_test extends \advanced_testcase {
    /** @var string|false Value of SENTRY_DSN before the test. */
    private $olddsn;

    protected function setUp(): void {
        parent::setUp();
        $this->olddsn = getenv('SENTRY_DSN');
        putenv('SENTRY_DSN');
        prepend::set_active_for_testing(false);
    }

    protected function tearDown(): void {
        prepend::set_active_for_testing(false);
        putenv($this->olddsn === false ? 'SENTRY_DSN' : 'SENTRY_DSN=' . $this->olddsn);
        parent::tearDown();
    }

    /**
     * Prepend mode needs both auto_prepend_file pointing to the plugin and a DSN.
     */
    public function test_is_configured(): void {
        $dsn = 'https://public@example.invalid/1';
        $this->assertTrue(prepend::is_configured(prepend::prepend_file(), $dsn));
        $this->assertFalse(prepend::is_configured(prepend::prepend_file(), ''));
        $this->assertFalse(prepend::is_configured('', $dsn));
        $this->assertFalse(prepend::is_configured(__FILE__, $dsn));
        $this->assertFalse(prepend::is_configured('/does/not/exist.php', $dsn));
        // Values of php.ini and of the environment: in PHPUnit there is no prepend file and no DSN.
        $this->assertFalse(prepend::is_configured());
    }

    /**
     * Without SENTRY_DSN, start() does nothing and the plugin keeps its usual behaviour.
     */
    public function test_start_without_dsn(): void {
        $this->assertFalse(prepend::start());
        $this->assertFalse(prepend::is_active());
    }

    /**
     * Without prepend mode, the helpers do nothing.
     */
    public function test_inactive_helpers(): void {
        $this->assertFalse(prepend::chain_handlers());
        $this->assertNull(prepend::capture(new \moodle_exception('error')));
    }

    /**
     * Handlers are chained once, only in prepend mode.
     */
    public function test_chain_handlers(): void {
        prepend::set_active_for_testing(true);
        try {
            $this->assertTrue(prepend::chain_handlers());
            $this->assertFalse(prepend::chain_handlers());
        } finally {
            // Each handler was replaced twice (null, then the chained closure).
            restore_exception_handler();
            restore_exception_handler();
            restore_error_handler();
            restore_error_handler();
        }
    }

    /**
     * Options read from the environment variables.
     */
    public function test_options(): void {
        $options = prepend::options(['SENTRY_DSN' => 'https://public@example.invalid/1']);
        $this->assertSame('https://public@example.invalid/1', $options['dsn']);
        $this->assertSame('production', $options['environment']);
        $this->assertNull($options['release']);
        $this->assertSame(1.0, $options['sample_rate']);
        $this->assertSame(prepend::DEFAULT_IGNORE_EXCEPTIONS, $options['ignore_exceptions']);
        $this->assertFalse($options['send_default_pii']);
        $this->assertSame('never', $options['max_request_body_size']);

        $options = prepend::options([
            'SENTRY_DSN' => 'https://public@example.invalid/1',
            'SENTRY_ENVIRONMENT' => 'staging',
            'SENTRY_RELEASE' => 'site@1.2.3',
            'SENTRY_SAMPLE_RATE' => '0.25',
            'SENTRY_IGNORE_EXCEPTIONS' => ' dml_exception , ,moodle_exception',
        ]);
        $this->assertSame('staging', $options['environment']);
        $this->assertSame('site@1.2.3', $options['release']);
        $this->assertSame(0.25, $options['sample_rate']);
        $this->assertSame(['dml_exception', 'moodle_exception'], $options['ignore_exceptions']);
    }

    /**
     * Component detection from the file path.
     */
    public function test_component(): void {
        $this->assertSame('mod_assign', prepend::component('/var/www/moodle/mod/assign/lib.php'));
        $this->assertSame('block_html', prepend::component('/var/www/moodle/blocks/html/block_html.php'));
        $this->assertSame('tool_sentry', prepend::component('C:\\moodle\\admin\\tool\\sentry\\lib.php'));
        $this->assertSame('core', prepend::component('/var/www/moodle/lib/setup.php'));
    }

    /**
     * Sensitive parameters never leave the server.
     */
    public function test_filter_query(): void {
        $this->assertSame('', prepend::filter_query(''));
        $this->assertSame(
            'id=3&wstoken=%5BFiltered%5D&Password=%5BFiltered%5D',
            prepend::filter_query('id=3&wstoken=abc&Password=xyz')
        );
    }

    /**
     * Events are tagged with the Moodle component and error code, and the URL is filtered.
     */
    public function test_before_send(): void {
        $exception = new \moodle_exception('invalidrecord', 'error');
        $event = \Sentry\Event::createEvent();
        $event->setRequest([
            'url' => 'https://moodle.example/login/token.php?token=abc&id=1',
            'query_string' => 'token=abc&id=1',
        ]);
        $hint = \Sentry\EventHint::fromArray(['exception' => $exception]);

        $event = prepend::before_send($event, $hint);

        $tags = $event->getTags();
        $this->assertSame('invalidrecord', $tags['moodle_errorcode']);
        $this->assertSame('error', $tags['moodle_module']);
        $this->assertArrayHasKey('moodle_component', $tags);
        $request = $event->getRequest();
        $this->assertSame('token=%5BFiltered%5D&id=1', $request['query_string']);
        $this->assertSame('https://moodle.example/login/token.php?token=%5BFiltered%5D&id=1', $request['url']);
        $this->assertNull($event->getUser());
    }
}
