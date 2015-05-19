<?php

/**
 * Pagina di visualizzazione del riepilogo degli studenti.
 *
 * @package    report
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

require(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/report/mpa/locallib.php');

$userid = $USER->id;
$usercontext = context_user::instance($userid);
print_page_attributes('pluginname', 'pluginname', $usercontext, 'report');

$renderer = $PAGE->get_renderer('report_mpa');

$result = array();
$data = array();

$students = $DB->get_records_sql('SELECT id FROM {user}');

foreach ($students as $student) {
    $result[$student->id] = array();
}


// Individuazione del numero di esercizi da valutare risolti per ciascun studente

$students = $DB->get_records_sql('SELECT * FROM {workshop_submissions} AS mws INNER JOIN {user} AS mu ON mws.authorid=mu.id');

if (!empty($students)) {
    foreach ($students as $student) {
        $data = array();
        $ex_to_evaluate_solved = $DB->count_records_sql('SELECT COUNT(*) FROM {workshop_submissions} WHERE authorid = ?', array($student->id));
        $data = $result[$student->id];
        array_push($data, $ex_to_evaluate_solved);
        $result[$student->id] = $data;
    }
} else {
    foreach ($result as $key => $value) {
        array_push($result[$key], 0);
    }
}

// Individuazione del numero di esercizi valutati per ciascun studente

$students = $DB->get_records_sql('SELECT * FROM {workshop_assessments} AS mwa INNER JOIN {user} AS mu ON mwa.reviewerid=mu.id');

if (!empty($students)) {
    foreach ($students as $student) {
        $data = array();
        $ex_assessed = $DB->count_records_sql('SELECT COUNT(*) FROM {workshop_assessments} WHERE reviewerid = ?', array($student->id));
        $data = $result[$student->id];
        array_push($data, $ex_assessed);
        $result[$student->id] = $data;
    }
} else {
    foreach ($result as $key => $value) {
        array_push($result[$key], 0);
    }
}

// Individuazione del numero di voti assegnati per ciascun studente

$students = $DB->get_records_sql('SELECT * FROM ({workshop_assessments} AS mwa INNER JOIN {workshop_grades} AS mwg ON mwa.id = mwg.assessmentid) INNER JOIN {user} AS mu ON mwa.reviewerid=mu.id');

if (!empty($students)) {
    foreach ($students as $student) {
        $data = array();
        $grades = $DB->count_records_sql('SELECT COUNT(*) FROM {workshop_assessments} AS mwa INNER JOIN {workshop_grades} AS mwg ON mwa.id = mwg.assessmentid WHERE reviewerid = ?', array($student->id));
        $data = $result[$student->id];
        array_push($data, $grades);
        $result[$student->id] = $data;
    }
} else {
    foreach ($result as $key => $value) {
        array_push($result[$key], 0);
    }
}

// Individuazione del numero di esercizi consegnati per ciascun studente

$students = $DB->get_records_sql('SELECT * FROM {assign} AS ma INNER JOIN {assign_submission} AS mas ON ma.id=mas.assignment ');

if (!empty($students)) {
    foreach ($students as $student) {
        $data = array();
        $assignments_solved = $DB->count_records_sql('SELECT COUNT(*) FROM {assign_submission} WHERE userid = ?', array($student->userid));
        $data = $result[$student->userid];
        array_push($data, $assignments_solved);
        $result[$student->userid] = $data;
    }
} else {
    foreach ($result as $key => $value) {
        array_push($result[$key], 0);
    }
}

$students_data = array();

if (!empty($students)) {
    foreach ($result as $student_id => $key_value) {
        if (sizeof($result[$student_id]) == 4) {
            $students_data[$student_id] = $result[$student_id];
        }
        if (sizeof($result[$student_id]) < 4 && sizeof($result[$student_id]) > 0) {
            array_push($result[$student_id], 0);
            $students_data[$student_id] = $result[$student_id];
        }
    }
} else {
    foreach ($result as $key => $value) {
        array_push($result[$key], 0);
    }
}


echo '<pre>';
print_r($students_data);
echo '</pre>';

echo $renderer->render_student_summary();



