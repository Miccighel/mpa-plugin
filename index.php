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

admin_externalpage_setup('mpa', '', null, '', array('pagelayout'=>'report'));

$PAGE->set_title(get_string('pluginname','report_mpa'));
$PAGE->set_heading(get_string('pluginname','report_mpa'), 3);

echo $OUTPUT->header();

echo "Le viste vanno stampate con i renderer";

echo $OUTPUT->footer();