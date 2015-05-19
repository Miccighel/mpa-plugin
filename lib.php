<?php
/**
 * Interazione con il core di Moodle.
 *
 * @package    report
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

function report_mpa_extend_navigation_user($navigation, $user, $course) {
    global $USER;

    if (isguestuser() or !isloggedin()) {
        return;
    }

    $context = context_user::instance($USER->id);

    $container = $navigation->add(get_string('basemenu','report_mpa'),navigation_node::TYPE_ROOTNODE);

    $url = new moodle_url('/report/mpa/views/studentsummary.php');
    $item = $container->add(get_string('mpa:studentsummary', 'report_mpa'), $url, navigation_node::TYPE_CONTAINER);

}