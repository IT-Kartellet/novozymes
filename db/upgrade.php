
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

    if ($oldversion < 2014091606) {
        $table = new xmldb_table('meta_views_log');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('metaid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('views_count_idx', XMLDB_INDEX_NOTUNIQUE, array('metaid'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        /* 
            DO THIS IN A SEPARATE SCRIPT, ON A SITE WITH A LOT OF LOG ENTRIES THIS WILL TIME OUT AND LEAVE THE SITE IN MAINTENANCE MODE WHILE UPDATING - BAD, MKAY!
       
            <?php
            define('CLI_SCRIPT', true);

            require(dirname(__FILE__).'/config.php'); // global moodle config file.

            // Upgrade all old log views
            $old_logs = $DB->get_records('log', array('module' => 'metacourse', 'action' => 'view'));

            $i = 0;
            foreach ($old_logs as $entry) {
                $i++;
                if ($i % 1000 === 0) {
                    echo $i . "\n";
                }

                $metaid = str_replace('view_metacourse.php?id=', '', $entry->url);
                $DB->insert_record('meta_views_log', array(
                    'metaid' => $metaid,
                    'user' => $entry->userid,
                    'timestamp' => $entry->time,
                ));
            }
            ?>
        */

        // Label savepoint reached.
        upgrade_block_savepoint(true, 2014091606, 'metacourse');
    }


    if ($oldversion < 2015022501) {
        // Add spanish lang
        $spanish = $DB->get_record('meta_languages', array('iso' => 'es'));

        if ($spanish) {
            $spanish->active = 1;
            $DB->update_record('meta_languages', $spanish);
        } else {
            $DB->insert_record('meta_languages', array('language' => 'Spanish', 'iso' => 'es', 'active' => 1));
        }

        // Change name of WE Representatives category to OH&S Representatives
        $representatives = $DB->get_record('meta_category', array('name' => 'WE Representatives'));
        if ($representatives) {
            $representatives->name = 'OH&S Representatives';
            $DB->update_record('meta_category', $representatives);
        }

        upgrade_block_savepoint(true, 2015022501, 'metacourse');
    }

    if ($oldversion < 2015120701) {
        // Add japanese lang
        $japanese = $DB->get_record('meta_languages', array('iso' => 'jp'));

        if ($japanese) {
            $japanese->active = 1;
            $DB->update_record('meta_languages', $japanese);
        } else {
            $DB->insert_record('meta_languages', array('language' => 'Japanese', 'iso' => 'jp', 'active' => 1));
        }

        upgrade_block_savepoint(true, 2015120701, 'metacourse');
    }


    if ($oldversion < 2016022902) {
        $table = new xmldb_table('meta_datecourse');
        $field = new xmldb_field('elearning', XMLDB_TYPE_INTEGER, '1');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $dbman->change_field_notnull($table, new xmldb_field('startdate', XMLDB_TYPE_INTEGER, '10', null, false));
        $dbman->change_field_notnull($table, new xmldb_field('enddate', XMLDB_TYPE_INTEGER, '10', null, false));

        upgrade_block_savepoint(true, 2016022902, 'metacourse');
    }

    if ($oldversion < 2016030101) {
        $table = new xmldb_table('meta_datecourse');
        $field = new xmldb_field('open');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_block_savepoint(true, 2016030101, 'metacourse');
    }

    if ($oldversion < 2016030105) {
        $table = new xmldb_table('meta_datecourse');

        $ix = new xmldb_index('metadate_loc_ix', XMLDB_INDEX_NOTUNIQUE, array('location'));

        if ($dbman->index_exists($table, $ix)) {
            $dbman->drop_index($table, $ix);
        }
        $dbman->change_field_notnull($table, new xmldb_field('total_places', XMLDB_TYPE_INTEGER, '10', null, false));
        $dbman->change_field_notnull($table, new xmldb_field('free_places', XMLDB_TYPE_INTEGER, '10', null, false));
        $dbman->change_field_notnull($table, new xmldb_field('location', XMLDB_TYPE_INTEGER, '10', null, false));
        $dbman->change_field_notnull($table, new xmldb_field('price', XMLDB_TYPE_TEXT, null, null, false));

        upgrade_block_savepoint(true, 2016030105, 'metacourse');
    }

    if ($oldversion < 2016030901) {
        $table = new xmldb_table('meta_datecourse');
        $field = new xmldb_field('free_places');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_block_savepoint(true, 2016030901, 'metacourse');
    }

    if ($oldversion < 2016031501) {
        $table = new xmldb_table('meta_datecourse');
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, 1, true, false, false, 0);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_block_savepoint(true, 2016031501, 'metacourse');
    }

    return $result;
}
