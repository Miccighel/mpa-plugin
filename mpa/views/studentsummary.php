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

$date = date('Y-m-d H:i:s');
$log = fopen("../log/Log_Summary.txt","a");
fwrite($log,"INIZIO DEL LOG PER L'ANALISI DELL'ATTIVITA DEGLI STUDENTI IN DATA: ".$date."\n");
fwrite($log,"INIZIO DELLA FASE DI ANALISI DELL'ATTIVITA DEGLI STUDENTI\n");
fwrite($log,"CI SONO ".count($final_students)." STUDENTI DA PROCESSARE\n");

$student_counter=1;

foreach ($final_students as $student) {

    fwrite($log,"ANALISI DELLLO STUDENTE NUMERO ".$student_counter." IN CORSO\n");

    $temp = $DB->get_records_sql('SELECT * FROM {mpa_student_summary} WHERE id=?', array($student->id));

    //Se lo studente non è presente nel db, viene inserito un nuovo record nella tabella con i dati relativi allo studente stesso, altrimenti il record stesso viene aggiornato

    if (empty($temp)) {
        $DB->execute('INSERT INTO {mpa_student_summary} (id,ex_to_evaluate_solved,ex_assessed,assigned_grades,received_grades,assignments_solved) VALUES (?,?,?,?,?,?)', $parms = array($student->id, $student->getExToEvaluateSolved(), $student->getExAssessed(), $student->getAssignedGrades(), $student->getReceivedGrades(), $student->getAssignmentsSolved()));
    } else {
        $DB->execute('UPDATE {mpa_student_summary} SET id=?, ex_to_evaluate_solved=?, ex_assessed=?, assigned_grades=?,received_grades=?, assignments_solved=? WHERE id=?', $parms = array($student->id, $student->getExToEvaluateSolved(), $student->getExAssessed(), $student->getAssignedGrades(), $student->getReceivedGrades(), $student->getAssignmentsSolved(), $student->id));
    }

    $student_counter++;

}

fwrite($log,"INIZIO DELLA FASE DI ANALISI DELL'ATTIVITA DEGLI STUDENTI\n");

fclose($log);

echo $renderer->render_student_summary($final_students);
