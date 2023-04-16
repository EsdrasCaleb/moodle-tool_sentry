<?php
namespace tool_sentry;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/admin/tool/sentry/vendor/autoload.php');

class helper {

    /**
     * Initialize sentry
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function init(\core\event\base $event) {
        $dns = get_config('tool_sentry', 'dns');
        if ($dns) {
            \Sentry\init(['dsn' => $dns]);
        }
    }

    /**
     * Get erros and send
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function geterros(\core\event\base $event) {
        $dns = get_config('tool_sentry', 'dns');
        if ($dns) {
            \Sentry\captureLastError();
        }
    }
}