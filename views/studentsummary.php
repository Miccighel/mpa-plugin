<?php

/**
 * Pagina di visualizzazione del riepilogo degli studenti.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

require(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/local/mpa/locallib.php');
require_once($CFG->dirroot . '/local/mpa/classes/student.php');

if (isloggedin()) {

    $userid = $USER->id;
    $usercontext = context_user::instance($userid);

    print_page_attributes('pluginname', 'pluginname', $usercontext, 'local');

} else {

    print_page_attributes('pluginname', 'pluginname', null, 'local');

}

$renderer = $PAGE->get_renderer('local_mpa');

$final_students = get_active_students();

foreach ($final_students as $student) {

    $temp = $DB->get_records_sql('SELECT * FROM {mpa_student_summary} WHERE id=?', array($student->id));

    //Se lo studente non è presente nel db, viene inserito un nuovo record nella tabella con i dati relativi allo studente stesso, altrimenti il record stesso viene aggiornato

    if (empty($temp)) {
        $DB->execute('INSERT INTO {mpa_student_summary} (id,ex_to_evaluate_solved,ex_assessed,assigned_grades,received_grades,assignments_solved) VALUES (?,?,?,?,?,?)', $parms = array($student->id, $student->getExToEvaluateSolved(), $student->getExAssessed(), $student->getAssignedGrades(), $student->getReceivedGrades(), $student->getAssignmentsSolved()));
    } else {
        $DB->execute('UPDATE {mpa_student_summary} SET id=?, ex_to_evaluate_solved=?, ex_assessed=?, assigned_grades=?,received_grades=?, assignments_solved=? WHERE id=?', $parms = array($student->id, $student->getExToEvaluateSolved(), $student->getExAssessed(), $student->getAssignedGrades(), $student->getReceivedGrades(), $student->getAssignmentsSolved(), $student->id));
    }

}

echo $renderer->render_student_summary($final_students);
