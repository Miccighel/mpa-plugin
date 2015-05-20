<?php

/**
 * Mizzaro Peer Assesment (MPA) Plugin.
 *
 * @package    report
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

require(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/mpa/locallib.php');


admin_externalpage_setup('mpa', '', null, '', array('pagelayout'=>'report'));
$userid = $USER->id;
$usercontext = context_user::instance($userid);
print_page_attributes('pluginname','pluginname',$usercontext,'report');

$renderer = $PAGE->get_renderer('report_mpa');

if(has_capability('report/mpa:reportoverview',$usercontext,$userid)){

    // Ottengo i dati da passare al renderer

    echo $renderer->render_report_overview();

} else {
    echo $renderer->render_capability_error();
}




