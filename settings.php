<?php
defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once $CFG->dirroot."/admin/tool/sentry/lib.php";
        
if (is_siteadmin()) {
    if (!$ADMIN->locate('tool_sentry')) {
        //plugin settings
        $page = new admin_settingpage('sentryconfig', get_string('pluginsettigs', 'tool_sentry'));
        $page->add(new admin_setting_configtext('tool_sentry/dns',get_string('dns', 'tool_sentry') ,'','https://USERCODE@o4504542192009216.ingest.sentry.io/CLIENTCODE'));
        /*
        $eventsArray = array('\core\event\course_viewed'=>'Course Viewn');
        $page->add(new admin_setting_configmultiselect('tool_sentry/events', get_string('monitorevents', 'tool_sentry'),'','',$tokenArray));
        */
        $ADMIN->add('tools',$page);

    }
}