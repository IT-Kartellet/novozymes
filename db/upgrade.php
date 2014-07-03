
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

     if ($result && $oldversion < 2014041406) {

        // Define table meta_tos_accept to be created.
        $table = new xmldb_table('meta_dates_waitlist');

        // Adding fields to table meta_tos_accept.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('datecourseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table meta_tos_accept.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('datecourseid', XMLDB_KEY_FOREIGN, array('datecourseid'), 'meta_datecourse', array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Conditionally launch create table for meta_tos_accept.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Label savepoint reached.
        upgrade_block_savepoint(true, 2014041406, 'metacourse');
    }

        if ($result && $oldversion < 2014041408) {

        // Define field remarks to be added to meta_datecourse.
        $table = new xmldb_table('meta_datecourse');
        $field = new xmldb_field('remarks', XMLDB_TYPE_TEXT, null, null, null, null, null, 'country');

        // Conditionally launch add field remarks.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Label savepoint reached.
        upgrade_block_savepoint(true, 2014041408, 'metacourse');
    }

    if ($oldversion < 2014041409) {

        // Define field nodates to be added to meta_waitlist.
        $table = new xmldb_table('meta_waitlist');
        $field = new xmldb_field('nodates', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'timecreated');

        // Conditionally launch add field nodates.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Label savepoint reached.
        upgrade_block_savepoint(true, 2014041409, 'metacourse');
    }

    if ($oldversion < 2014070301) {

        // Define field timezone to be added to meta_datecourse.
        $table = new xmldb_table('meta_datecourse');
        $field = new xmldb_field('timezone', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, '0', 'enddate');

        // Conditionally launch add field timezone.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Label savepoint reached.
        upgrade_block_savepoint(true, 2014070301, 'metacourse');
    }


    return $result;
}