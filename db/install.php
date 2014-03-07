<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_block_metacourse_install() {
    global $CFG, $DB;

    $records = array();
    $records[] = array("language"=>'Arabic', "iso"=>'ar');
    $records[] = array("language"=>'Armenian', "iso"=>'hy');
    $records[] = array("language"=>'Bulgarian', "iso"=>'bg');
    $records[] = array("language"=>'Catalan', "iso"=>'ca');
    $records[] = array("language"=>'Czech', "iso"=>'cs');
    $records[] = array("language"=>'Chinese', "iso"=>'zh_cn');
    $records[] = array("language"=>'Croatian', "iso"=>'hr');
    $records[] = array("language"=>'Danish',"active"=>1,"iso"=>'da');
    $records[] = array("language"=>'Dutch', "iso"=>'nl');
    $records[] = array("language"=>'English',"active"=>1,"iso"=>'en');
    $records[] = array("language"=>'Estonian', "iso"=>'et');
    $records[] = array("language"=>'Finnish', "iso"=>'fi');
    $records[] = array("language"=>'French', "iso"=>'fr');
    $records[] = array("language"=>'German', "iso"=>'de');
    $records[] = array("language"=>'Greek', "iso"=>'el');
    $records[] = array("language"=>'Hebrew', "iso"=>'he');
    $records[] = array("language"=>'Hindi', "iso"=>'hi');
    $records[] = array("language"=>'Hungarian', "iso"=>'hu');
    $records[] = array("language"=>'Icelandic', "iso"=>'is');
    $records[] = array("language"=>'Indonesian', "iso"=>'id');
    $records[] = array("language"=>'Italian', "iso"=>'it');
    $records[] = array("language"=>'Japanese', "iso"=>'ja');
    $records[] = array("language"=>'Korean', "iso"=>'ko');
    $records[] = array("language"=>'Laotian', "iso"=>'lo');
    $records[] = array("language"=>'Latvian', "iso"=>'lv');
    $records[] = array("language"=>'Lithuanian', "iso"=>'lt');
    $records[] = array("language"=>'Macedonian', "iso"=>'mk');
    $records[] = array("language"=>'Norwegian', "iso"=>'no');
    $records[] = array("language"=>'Polish', "iso"=>'pl');
    $records[] = array("language"=>'Portuguese', "iso"=>'pt');
    $records[] = array("language"=>'Romanian', "iso"=>'ro');
    $records[] = array("language"=>'Russian', "iso"=>'ru');
    $records[] = array("language"=>'Serbian', "iso"=>'sr_cr');
    $records[] = array("language"=>'Slovak', "iso"=>'sk');
    $records[] = array("language"=>'Slovenian', "iso"=>'sl');
    $records[] = array("language"=>'Somali', "iso"=>'so');
    $records[] = array("language"=>'Spanish', "iso"=>'es');
    $records[] = array("language"=>'Swedish', "iso"=>'sv');
    $records[] = array("language"=>'Turkish', "iso"=>'tr');
    $records[] = array("language"=>'Ucrainian', "iso"=>'uk');
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
