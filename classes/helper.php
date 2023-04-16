<?php
namespace tool_sentry;

defined('MOODLE_INTERNAL') || die();

class helper {

    /**
     * Initialize sentry
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function init(\core\event\base $event) {
        global $CFG;
        $dns = get_config('tool_sentry', 'dns');
        if ($dns) {
            global $CFG;
            require_once($CFG->dirroot.'/admin/tool/sentry/vendor/autoload.php');
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
        global $CFG;
        $dns = get_config('tool_sentry', 'dns');
        if ($dns) {
            global $CFG;
            require_once($CFG->dirroot.'/admin/tool/sentry/vendor/autoload.php');
            \Sentry\captureLastError();
            algo();
        }
    }
}
