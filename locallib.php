<?php
/**
 * Business logic del plugin.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

function print_page_attributes($title,$heading,$context,$layout){
    global $PAGE;
    $PAGE->set_title(get_string($title,'local_mpa'));
    $PAGE->set_heading(get_string($heading,'local_mpa'), 3);
    $PAGE->set_context($context);
    $PAGE->set_pagelayout($layout);
}

// Intuitivamente, ritorna gli studenti che hanno fatto qualcosa.

function get_active_students(){

    global $DB;

    $all_students = array();
    $final_students = array();

    $students = $DB->get_records_sql('SELECT id FROM {user}');

    foreach ($students as $student) {
        $object = new Student($student->id);
        $object->loadStudentProperties();
        $object->loadStudentActivity();
        $object->countAssignmentsSolved();
        $object->countExAssessed();
        $object->countExToEvaluateSolved();
        $object->countGrades();
        array_push($all_students, $object);
    }

    foreach ($all_students as $student) {
        if ($student->getGrades() != 0 || $student->getExAssessed() != 0 || $student->getExToEvaluateSolved() != 0 || $student->getAssignmentsSolved() != 0) {
            array_push($final_students, $student);
        }
    }

    return $final_students;
}