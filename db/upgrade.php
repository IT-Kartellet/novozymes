<?php

function xmldb_block_metacourse_upgrade($oldversion = 0) {
    global $DB;
    $dbman = $DB->get_manager();

    $result = true;

    
        if ($result && $oldversion < 2014041404) {

            // Define table meta_countries to be created.
            $table = new xmldb_table('meta_countries');

            // Adding fields to table meta_countries.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('country', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

            // Adding keys to table meta_countries.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            // Conditionally launch create table for meta_countries.
            if (!$dbman->table_exists($table)) {
                $dbman->create_table($table);
            }

            // Label savepoint reached.
            upgrade_block_savepoint(true, 2014041404, 'metacourse');


            // Define field country to be added to meta_datecourse.
            $table = new xmldb_table('meta_datecourse');
            $field = new xmldb_field('country', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');

            // Conditionally launch add field country.
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            // Label savepoint reached.
            upgrade_block_savepoint(true, 2014041404, 'metacourse');
    }


    return $result;
}