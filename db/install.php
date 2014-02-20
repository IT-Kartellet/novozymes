<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_block_metacourse_install() {
    global $CFG, $DB;

    $records = array();
    $records[] = array("language"=>'Arabic');
    $records[] = array("language"=>'Armenian');
    $records[] = array("language"=>'Bulgarian');
    $records[] = array("language"=>'Catalan');
    $records[] = array("language"=>'Czech');
    $records[] = array("language"=>'Chinese');
    $records[] = array("language"=>'Croatian');
    $records[] = array("language"=>'Danish',"active"=>1);
    $records[] = array("language"=>'Dutch');
    $records[] = array("language"=>'English',"active"=>1);
    $records[] = array("language"=>'Estonian');
    $records[] = array("language"=>'Faroese');
    $records[] = array("language"=>'Finnish');
    $records[] = array("language"=>'French');
    $records[] = array("language"=>'German');
    $records[] = array("language"=>'Greek');
    $records[] = array("language"=>'Hebrew');
    $records[] = array("language"=>'Hindi');
    $records[] = array("language"=>'Hungarian');
    $records[] = array("language"=>'Icelandic');
    $records[] = array("language"=>'Indonesian');
    $records[] = array("language"=>'Italian');
    $records[] = array("language"=>'Japanese');
    $records[] = array("language"=>'Korean');
    $records[] = array("language"=>'Laotian');
    $records[] = array("language"=>'Latvian');
    $records[] = array("language"=>'Lappish');
    $records[] = array("language"=>'Lithuanian');
    $records[] = array("language"=>'Macedonian');
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
    $records[] = array("language"=>'Tibetan');
    $records[] = array("language"=>'Turkish');
    $records[] = array("language"=>'Ucrainian');
    foreach ($records as $rec) {
    	$DB->insert_record("meta_languages", $rec);
    }

    $tos = array("tos"=>"Terms of service example. Some text here that the users will agree with, in order to get enrolled.");
    $DB->insert_record("meta_tos", $tos);

    $currencies = array();
    $currencies[] = array("currency"=>"EUR");
    $currencies[] = array("currency"=>"BGN");
    $currencies[] = array("currency"=>"GBP");
    $currencies[] = array("currency"=>"HRK");
    $currencies[] = array("currency"=>"CZK");
    $currencies[] = array("currency"=>"DKK");
    $currencies[] = array("currency"=>"HUF");
    $currencies[] = array("currency"=>"LVL");
    $currencies[] = array("currency"=>"LTL");
    $currencies[] = array("currency"=>"PLN");
    $currencies[] = array("currency"=>"RON");
    $currencies[] = array("currency"=>"SEK");
    $currencies[] = array("currency"=>"CHF");
    $currencies[] = array("currency"=>"USD");
    $currencies[] = array("currency"=>"RUB");
    $currencies[] = array("currency"=>"NOK");
    $currencies[] = array("currency"=>"TRY");
    $currencies[] = array("currency"=>"UAH");
    $currencies[] = array("currency"=>"MDL");
    $currencies[] = array("currency"=>"JPY");
    $currencies[] = array("currency"=>"AUD");
    $currencies[] = array("currency"=>"CAD");
    $currencies[] = array("currency"=>"HKD");

    foreach ($currencies as $cur) {
        $DB->insert_record("meta_currencies", $cur);
    }

}
