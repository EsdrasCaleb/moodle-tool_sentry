<?php
defined('MOODLE_INTERNAL') || die;
global $CFG;

if (is_siteadmin()) {
    if (!$ADMIN->locate('tool_sentry')) {
        //plugin settings
        $page = new admin_settingpage('sentryconfig', get_string('pluginsettigs', 'tool_sentry'));
        $page->add(new admin_setting_configtext('tool_sentry/dns',get_string('dns', 'tool_sentry') ,'','https://de9f084379174381ac9d1bf8c3f7d561@o4504542192009216.ingest.sentry.io/4504542194302976'));
        /*
        $eventsArray = array('mssql'=>'Microsoft SQL SERVER');
        $page->add(new admin_setting_configmultiselect('tool_sentry/events', get_string('monitorevents', 'tool_sentry'),'','',$tokenArray));
        */
        $ADMIN->add('tools',$page);

    }
}