<?php

function xmldb_tool_sentry_upgrade($oldversion): bool {
    global $CFG, $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2024071200) {
        $DB->delete_records("mdl_config_plugins",[ 'plugin'=>'tool_sentry','name'=>'dns']);
        upgrade_plugin_savepoint(true, 2024071200, 'tool', 'sentry');
    }

    return true;
}