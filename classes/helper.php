<?php 
defined('MOODLE_INTERNAL') || die();
class tool_sentry_helper {

    /**
     * Observe the events, and dispatch them if necessary.
     * Todos os eventos disparados devem ser tratados aqui.
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function observer(\core\event\base $event) {
        global $CFG;
        require_once $CFG->dirroot."/admin/tool/sentry/lib.php";
    }
}