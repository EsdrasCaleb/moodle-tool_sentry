<?php

defined('MOODLE_INTERNAL') || die();
/**
 * Just check for errors.
 */
function checkbugs(){
    $dns = get_config('tool_sentry', 'dns');
    if ($dns) {
        global $CFG;
        $oldDebug = $CFG->debug;
        $CFG->debug = (E_ALL | E_STRICT); 
        require_once($CFG->dirroot.'/admin/tool/sentry/vendor/autoload.php');
        \Sentry\captureLastError();
        $CFG->debug = $oldDebug;
    }

}

/**
 * Initialize sentry and check for errors.
 */
function initializecheck(){
    $dns = get_config('tool_sentry', 'dns');
    if ($dns) {
        global $CFG;
        $oldDebug = $CFG->debug;
        $CFG->debug = (E_ALL | E_STRICT); 
        require_once($CFG->dirroot.'/admin/tool/sentry/vendor/autoload.php');
        \Sentry\init(['dsn' => $dns]);
        \Sentry\captureLastError();
        $CFG->debug = $oldDebug;
    }
}
initializecheck();
