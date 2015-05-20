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

// Impostazione di un array associativo dove la chiave è l'id dello studente.
foreach ($students as $student) {
    $result[$student->id] = array();
}

// Individuazione del numero di esercizi da valutare risolti per ciascun studente

$students = $DB->get_records_sql('SELECT * FROM {workshop_submissions} AS mws INNER JOIN {user} AS mu ON mws.authorid=mu.id');

if (!empty($students)) {
    foreach ($students as $student) {
        $data = array();
        // Ottengo il conteggio di quello che ho ottenuto con la query precedente in base all'id dello studente.
        $ex_to_evaluate_solved = $DB->count_records_sql('SELECT COUNT(*) FROM {workshop_submissions} WHERE authorid = ?', array($student->id));
        // Ottengo l'array di dati del singolo studente salvati nell'array associativo, dove la chiave è l'id dello studente stesso.
        $data = $result[$student->id];
        // Inserisco il nuovo dato nell'array dello studente.
        array_push($data, $ex_to_evaluate_solved);
        // Aggiorno il l'array salvato in quello associativo globale con il nuovo array dedicato allo studente.
        $result[$student->id] = $data;
    }
} else {
    // Se non c'è almeno uno studente che abbia completato almeno una volta il task cercato, vengono inseriti zeri per tutti gli studenti.
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
    // Per ogni studente presente nel db
    foreach ($result as $student_id => $key_value) {
        // Se lo studente ha quattro campi viene inserito nel vettore che verrà passato al renderer
        if (sizeof($result[$student_id]) == 4) {
            $students_data[$student_id] = $result[$student_id];
        }
        /* Se a questo punto un campo dell'array associativo ha meno di quattro elementi ma più di uno è perchè l'ultima query
        non ha individuato assignment risolti per lo studente in oggetto, ma le query precedenti rilevano che ha svolto assessment ed assegnato valutazioni.
        Di conseguenza, va messo comunque tra i dati passati al renderer perchè dovrà essere mostrato nel riepilogo.
        Viene dunque inserito uno zero tra i suoi dati ad indicare che non ha svolto, appunto, alcun assignment. */
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

// Per ogni studente di cui sono stati calcolati i dati riassuntivi
foreach($students_data as $student_id => $student_data){

    $test = $DB->get_records_sql('SELECT id FROM {mpa_student_summary} WHERE id=?',array($student_id));

    // Se lo studente non è presente nel db, viene inserito un nuovo record nella tabella con i dati relativi allo studente stesso, altrimenti il record stesso viene aggiornato
    if(empty($test)){
       $DB->execute('INSERT INTO {mpa_student_summary} (id,ex_to_evaluate_solved,ex_assessed,grades,assignments_solved) VALUES (?,?,?,?,?)', $parms=array($student_id,$student_data[0],$student_data[1],$student_data[2],$student_data[3]));
    } else {
        $DB->execute('UPDATE {mpa_student_summary} SET id=?, ex_to_evaluate_solved=?, ex_assessed=?, grades=?, assignments_solved=? WHERE id=?', $parms=array($student_id,$student_data[0],$student_data[1],$student_data[2],$student_data[3],$student_id));
    }
}

echo $renderer->render_student_summary($students_data);