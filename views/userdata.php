<?php

/**
 * Pagina di visualizzazione dei dati di tutti gli utenti.
 *
 * @package    report
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

require(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/report/mpa/locallib.php');

$userid = $USER->id;
$usercontext = context_user::instance($userid);
print_page_attributes('pluginname','pluginname',$usercontext,'report');

$renderer = $PAGE->get_renderer('report_mpa');

if(has_capability('report/mpa:viewuserdata',$usercontext,$userid)){

    // Ottengo i dati da passare al renderer

    echo $renderer->render_view_user_data();

} else {
    echo $renderer->render_capability_error();
}


