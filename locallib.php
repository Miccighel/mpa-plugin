<?php
/**
 * Business logic del plugin.
 *
 * @package    report
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

function print_page_attributes($title,$heading,$context,$layout){
    global $PAGE;
    $PAGE->set_title(get_string($title,'report_mpa'));
    $PAGE->set_heading(get_string($heading,'report_mpa'), 3);
    $PAGE->set_context($context);
    $PAGE->set_pagelayout($layout);
}