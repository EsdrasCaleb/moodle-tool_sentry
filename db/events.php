<?php

defined('MOODLE_INTERNAL') || die();

$observers = array();

$observers[] = array(
    'eventname' => 'core\event\base',
    'callback' => '\tool_sentry\helper::init',
    'internal' => true,
    'priority'    => 9999,
);

$observers[] = array(
    'eventname' => 'core\event\base',
    'callback' => '\tool_sentry\helper::geterros',
    'internal' => true,
    'priority'    => 0,
);
