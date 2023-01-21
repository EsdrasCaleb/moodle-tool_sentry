<?php 

$observers = array();


//Evento apara o forum

$observers[] = array(
    'eventname' => 'core\event\course_viewed',
    'callback' => 'tool_sentry_helper::observer',
    'includefile' => '/admin/tool/sentry/classes/helper.php',
    'internal' => true,
    'priority'    => 9999,
);



