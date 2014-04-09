<?php

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . "/lib.php");


function xmldb_block_metacourse_install() {
    global $CFG, $DB;

    $records = array();
    $records[] = array("language"=>'Arabic', "iso"=>'ar');
    $records[] = array("language"=>'Armenian', "iso"=>'hy');
    $records[] = array("language"=>'Bulgarian', "iso"=>'bg');
    $records[] = array("language"=>'Catalan', "iso"=>'ca');
    $records[] = array("language"=>'Czech', "iso"=>'cs');
    $records[] = array("language"=>'Chinese',"active"=>1, "iso"=>'zh_cn');
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
    $records[] = array("language"=>'Portuguese',"active"=>1, "iso"=>'pt');
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
    $currencies[] = array("currency"=>"EUR"); // euros
    $currencies[] = array("currency"=>"BRL"); // brazilian
    $currencies[] = array("currency"=>"GBP"); // british pounds
    $currencies[] = array("currency"=>"CNY"); // chinese
    $currencies[] = array("currency"=>"CHF"); // swiss franc
    $currencies[] = array("currency"=>"DKK"); // danish
    $currencies[] = array("currency"=>"INR"); // indian
    $currencies[] = array("currency"=>"USD"); // us dollars

    foreach ($currencies as $cur) {
        $DB->insert_record("meta_currencies", $cur);
    }

    $providers = array();
    $providers[] = array("provider"=>"P&O Development & Performance");
    $providers[] = array("provider"=>"P&O Asia Pacific");
    $providers[] = array("provider"=>"P&O Latin America");
    $providers[] = array("provider"=>"P&O North America");
    $providers[] = array("provider"=>"P&O India");
    $providers[] = array("provider"=>"P&O Europe");
    $providers[] = array("provider"=>"P&O Denmark");
    $providers[] = array("provider"=>"Global P&O");
    $providers[] = array("provider"=>"BO/BD");
    $providers[] = array("provider"=>"QES (PI)");
    $providers[] = array("provider"=>"ProMan");
    $providers[] = array("provider"=>"Patents, Licensing & Strategy (L&S), Legal Affairs");
    $providers[] = array("provider"=>"Sales Management");
    $providers[] = array("provider"=>"Finance");
    $providers[] = array("provider"=>"Sales & Marketing");
    $providers[] = array("provider"=>"Global Marketing");
    $providers[] = array("provider"=>"Global OHS");
    $providers[] = array("provider"=>"Corporate Communications & Branding");
    $providers[] = array("provider"=>"Sourcing");
    $providers[] = array("provider"=>"QM");
    $providers[] = array("provider"=>"Sund i NZ (Only DK)");
    $providers[] = array("provider"=>"Medical Centre");
    $providers[] = array("provider"=>"NZIT Development Services");
    $providers[] = array("provider"=>"R&D");
    $providers[] = array("provider"=>"PI-Project");
    $providers[] = array("provider"=>"Bioinformatics");
    $providers[] = array("provider"=>"Organizational Business Support");
    $providers[] = array("provider"=>"SCS");
    $providers[] = array("provider"=>"Maintenance");
    $providers[] = array("provider"=>"Treasury");

    foreach ($providers as $pro) {
        create_role_and_provider($pro['provider']);
    }

}
