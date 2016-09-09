
<?php

//require_once('../lib.php');

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
	
	if ($oldversion < 2016053001) {
		$table = new xmldb_table('meta_course');
		$field = new xmldb_field('price', XMLDB_TYPE_TEXT, null, null, null, null, null, 'duration_unit');
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		$field = new xmldb_field('currencyid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'price');
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		upgrade_block_savepoint(true, 2016053001, 'metacourse');
	}
	
	if ($oldversion < 2016060101) {
		$table = new xmldb_table('meta_course');
		$table->add_key('currencyid', XMLDB_KEY_FOREIGN, array('currencyid'), 'meta_currencies', array('id'));
		
		upgrade_block_savepoint(true, 2016060101, 'metacourse');
	}
	
	if ($oldversion < 2016060201) {
		$table = new xmldb_table('meta_datecourse');
		$field = new xmldb_field('realunpublishdate', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'unpublishdate');
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		upgrade_block_savepoint(true, 2016060201, 'metacourse');
	}
	
	if ($oldversion < 2016060701) {
		$table = new xmldb_table('meta_course');
		$field = new xmldb_field('nodates_enabled', XMLDB_TYPE_INTEGER, 1, true, false, false, 0, 'unpublishdate');
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$table = new xmldb_table('meta_datecourse');
		$field = new xmldb_field('manual_enrol', XMLDB_TYPE_INTEGER, 1, true, false, false, 0, 'elearning');
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		upgrade_block_savepoint(true, 2016060701, 'metacourse');
	}
	
	if ($oldversion < 2016060702) {
		$table = new xmldb_table('meta_template');
		$field = new xmldb_field('price', XMLDB_TYPE_TEXT, null, null, null, null, null, 'duration_unit');
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		$field = new xmldb_field('currencyid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'price');
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		$field = new xmldb_field('nodates_enabled', XMLDB_TYPE_INTEGER, 1, true, false, false, 0, 'unpublishdate');
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		$table->add_key('currencyid', XMLDB_KEY_FOREIGN, array('currencyid'), 'meta_currencies', array('id'));
		
		upgrade_block_savepoint(true, 2016060702, 'metacourse');
	}
	
	if ($oldversion < 2016081901) {
		$table = new xmldb_table('meta_locations');
		$field = new xmldb_field('timezonename', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'location');
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$table = new xmldb_table('meta_datecourse');
		$field = new xmldb_field('timezonename', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'timezone');
		if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$DB->set_field('meta_locations', 'timezonename', 'Europe/Copenhagen');
		
		upgrade_block_savepoint(true, 2016081901, 'metacourse');
	}
	
	function format_tz_offset($offset) {
	  if (strstr($offset, ':30')) {
		// Convert from xx:30 to xx.5 so we can multiply by it
		// 30 minutes = 0.5 hour. I hope we do something smarter before we have to take :45 offsets into account ...
		$offset = intval($offset);

		if ($offset >= 0) {
		  $offset += .5;
		} else {
		  $offset -= .5;
		}
	  }

	  return $offset;
	}
	if ($oldversion < 2016081902) {
		$rs = $DB->get_records_sql("SELECT c.id, c.timezone, c.startdate, c.enddate, c.location, l.location AS locationname, l.timezonename, c.metaid, c.deleted FROM meta_datecourse AS c left join meta_locations AS l ON c.location = l.id where elearning is null or elearning = 0");
		foreach ($rs as $key => $rec) {
			$offs = 3600 * format_tz_offset($rec->timezone);
			if ($rec->timezonename==null) $tznm = 'Europe/Copenhagen';
			else $tznm = $rec->timezonename;
			$tz = new DateTimeZone($tznm);
			$start = new DateTime();
			$start->setTimestamp($rec->startdate);
			$tzoffs = $tz->getOffset($start);
			
			if ($offs!=$tzoffs) {
				$tznm = timezone_name_from_abbr("", $offs, 0);
				if ($tznm!==false) {
					$tz = new DateTimeZone($tznm);
					$tzoffs = $tz->getOffset($start);
				}
			}
			if ($offs!=$tzoffs) {
				$tznm = timezone_name_from_abbr("", $offs, 1);
				if ($tznm!==false) {
					$tz = new DateTimeZone($tznm);
					$tzoffs = $tz->getOffset($start);
				}
			}
			
			if ($offs==$tzoffs) {
				$end = new DateTime();
				$end->setTimestamp($rec->enddate);
				$tzoffs = $tz->getOffset($end);
				if ($offs==$tzoffs) {
					$DB->set_field('meta_datecourse', 'timezonename', $tznm, array('id' => $rec->id));
				}
			}
		}
		upgrade_block_savepoint(true, 2016081902, 'metacourse');
	}
	
	if ($oldversion < 2016091101) {
		$DB->execute('CREATE TABLE meta_datecourse_backup LIKE meta_datecourse');
		$DB->execute('INSERT meta_datecourse_backup SELECT * FROM meta_datecourse');
		
		$rs = $DB->get_records_sql("SELECT c.id, c.timezone, c.startdate, c.enddate, c.location, l.location AS locationname, l.timezonename, c.metaid, c.deleted FROM meta_datecourse AS c left join meta_locations AS l ON c.location = l.id where (elearning is null or elearning = 0) order by c.id");
		foreach ($rs as $key => $rec) {
			$dc = new stdClass();
			$dc->id = $rec->id;
			
			$offs = 3600 * format_tz_offset($rec->timezone);
			
			// Update timezone from location.
			if ($rec->timezonename==null) $tznm = 'Europe/Copenhagen';
			else $tznm = $rec->timezonename;
			$dc->timezonename = $tznm;
			//$DB->set_field('meta_datecourse', 'timezonename', $tznm, array('id' => $rec->id));
			
			// Get corresponding offset (use start date as base).
			$tz = new DateTimeZone($tznm);
			$dt = new DateTime();
			$dt->setTimestamp($rec->startdate);
			$tzoffs = $tz->getOffset($dt);
			$tzoffs2 = $tzoffs / 3600;
			if ($tzoffs2 >= 0) {
				$h = floor($tzoffs2);
				$m = 60 * ($tzoffs2 - $h);
				if ($m == 0) $dc->timezone = '+' . $h;
				else $dc->timezone = '+' . $h . ':' . $m;
				//if ($m == 0) $DB->set_field('meta_datecourse', 'timezone', '+' . $h, array('id' => $rec->id));
				//else $DB->set_field('meta_datecourse', 'timezone', '+' . $h . ':' . $m, array('id' => $rec->id));
			}
			else {
				$h = floor(-$tzoffs2);
				$m = 60 * (-$tzoffs2 - $h);
				if ($m == 0) $dc->timezone = '-' . $h;
				else $dc->timezone = '-' . $h . ':' . $m;
				//if ($m == 0) $DB->set_field('meta_datecourse', 'timezone', '-' . $h, array('id' => $rec->id));
				//else $DB->set_field('meta_datecourse', 'timezone', '-' . $h . ':' . $m, array('id' => $rec->id));
			}
			
			// Correct start and end time if neccesary for new time zone.
			if ($offs!=$tzoffs) $dc->startdate = $rec->startdate + $offs - $tzoffs;
			//if ($offs!=$tzoffs) $DB->set_field('meta_datecourse', 'startdate', $rec->startdate + $offs - $tzoffs, array('id' => $rec->id));
			$dt = new DateTime();
			$dt->setTimestamp($rec->enddate);
			$tzoffs = $tz->getOffset($dt);
			if ($offs!=$tzoffs) $dc->enddate = $rec->enddate + $offs - $tzoffs;
			//if ($offs!=$tzoffs) $DB->set_field('meta_datecourse', 'enddate', $rec->enddate + $offs - $tzoffs, array('id' => $rec->id));
			
			//$dc->timemodified = time();
			//$DB->set_field('meta_datecourse', 'timemodified', time(), array('id' => $rec->id));
			
			$DB->update_record('meta_datecourse', $dc);
		}
		upgrade_block_savepoint(true, 2016091101, 'metacourse');
	}

    return $result;
}
