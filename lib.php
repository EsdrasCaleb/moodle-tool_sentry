<?php

defined('MOODLE_INTERNAL') || die();


/**
 * Initialize sentry and check for errors.
 */
function init_sentry(){
    $dns = get_config('tool_sentry', 'dns');
    if ($dns) {
        global $CFG;
        require_once($CFG->dirroot.'/admin/tool/sentry/vendor/autoload.php');
        \Sentry\init(['dsn' => $dns]);
    }
}

init_sentry();