<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_block_metacourse_install() {
    global $CFG, $DB;

    $records = array();
    $records[] = array("language"=>'Arabic');
    $records[] = array("language"=>'Armenian');
    $records[] = array("language"=>'Bengali');
    $records[] = array("language"=>'Byelorussian');
    $records[] = array("language"=>'Bulgarian');
    $records[] = array("language"=>'Catalan');
    $records[] = array("language"=>'Czech');
    $records[] = array("language"=>'Chinese');
    $records[] = array("language"=>'Croatian');
    $records[] = array("language"=>'Danish');
    $records[] = array("language"=>'Dutch');
    $records[] = array("language"=>'English');
    $records[] = array("language"=>'Estonian');
    $records[] = array("language"=>'Faroese');
    $records[] = array("language"=>'Farsi');
    $records[] = array("language"=>'Finnish');
    $records[] = array("language"=>'French');
    $records[] = array("language"=>'German');
    $records[] = array("language"=>'Greek');
    $records[] = array("language"=>'Hebrew');
    $records[] = array("language"=>'Hindi');
    $records[] = array("language"=>'Hungarian');
    $records[] = array("language"=>'Icelandic');
    $records[] = array("language"=>'Indonesian');
    $records[] = array("language"=>'Inuktitut (Eskimo)');
    $records[] = array("language"=>'Italian');
    $records[] = array("language"=>'Japanese');
    $records[] = array("language"=>'Korean');
    $records[] = array("language"=>'Laotian');
    $records[] = array("language"=>'Latvian');
    $records[] = array("language"=>'Lappish');
    $records[] = array("language"=>'Lithuanian');
    $records[] = array("language"=>'Macedonian');
    $records[] = array("language"=>'Malay');
    $records[] = array("language"=>'Maltese');
    $records[] = array("language"=>'Nepali');
    $records[] = array("language"=>'Norwegian');
    $records[] = array("language"=>'Polish');
    $records[] = array("language"=>'Portuguese');
    $records[] = array("language"=>'Romanian');
    $records[] = array("language"=>'Russian');
    $records[] = array("language"=>'Serbian');
    $records[] = array("language"=>'Slovak');
    $records[] = array("language"=>'Slovenian');
    $records[] = array("language"=>'Somali');
    $records[] = array("language"=>'Spanish');
    $records[] = array("language"=>'Swedish');
    $records[] = array("language"=>'Swahili');
    $records[] = array("language"=>'Tagalog-Filipino');
    $records[] = array("language"=>'Tajik');
    $records[] = array("language"=>'Tamil');
    $records[] = array("language"=>'Thai');
    $records[] = array("language"=>'Tibetan');
    $records[] = array("language"=>'Tongan');
    $records[] = array("language"=>'Turkish');
    $records[] = array("language"=>'Turkmen');
    $records[] = array("language"=>'Ucrainian');
    $records[] = array("language"=>'Urdu');
    $records[] = array("language"=>'Uzbek');
    $records[] = array("language"=>'Vietnamese');
    foreach ($records as $rec) {
    	$DB->insert_record("meta_languages", $rec);
    }

    $tos = array("tos"=>"Terms of service example. Some text here that the users will agree with, in order to get enrolled.");
    $DB->insert_record("meta_tos", $tos);
}
