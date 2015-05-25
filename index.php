<?php

/**
 * Mizzaro Peer Assesment (MPA) Plugin.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

require(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/local/mpa/locallib.php');

$userid = $USER->id;
$usercontext = context_user::instance($userid);
print_page_attributes('pluginname','pluginname',$usercontext,'local');

$renderer = $PAGE->get_renderer('local_mpa');

if(has_capability('local/mpa:localoverview',$usercontext,$userid)){

    // Ottengo i dati da passare al renderer

    echo $renderer->render_local_overview();

} else {
    echo $renderer->render_capability_error();
}




