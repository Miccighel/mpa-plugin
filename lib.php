<?php
/**
 * Interazione con il core di Moodle.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

/*function local_mpa_extend_navigation_user($navigation, $user, $course) {
    global $USER;

    if (isguestuser() or !isloggedin()) {
        return;
    }

    $context = context_user::instance($USER->id);

    $container = $navigation->add(get_string('basemenu','local_mpa'),navigation_node::TYPE_ROOTNODE);

    $url = new moodle_url('/local/mpa/views/studentsummary.php');
    $item = $container->add(get_string('mpa:studentsummary', 'local_mpa'), $url, navigation_node::TYPE_CONTAINER);

}*/

function local_mpa_extends_navigation(global_navigation $navigation) {
    $nodeFoo = $navigation->add('Foo');
    $nodeBar = $nodeFoo->add('Bar');
}
