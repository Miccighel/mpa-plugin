<?php

/**
 * Pagina di visualizzazione dei punteggi risolutore e valutatore per tutti gli studenti.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

require(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/local/mpa/locallib.php');
require_once($CFG->dirroot . '/local/mpa/classes/student.php');

define('MULTIPLIER',100);
define('EPSILON',0.01);

if (isloggedin()) {

    $userid = $USER->id;
    $usercontext = context_user::instance($userid);
    print_page_attributes('pluginname', 'pluginname', $usercontext, 'local');

    $renderer = $PAGE->get_renderer('local_mpa');


    if (has_capability('local/mpa:studentscore', $usercontext, $userid)) {

        $submissions_data = $DB->get_records_sql('SELECT *
        FROM {workshop_submissions} AS mws INNER JOIN {workshop_assessments} AS mwa ON mwa.submissionid=mws.id WHERE mwa.reviewerid!=mws.authorid', array());

        foreach ($submissions_data as $submission_data){

            // ID della risoluzione
            $submissionid = $submission_data->submissionid;
            // Autore dell'assessment per la risoluzione
            $evaluatorid = $submission_data->reviewerid;
            // Autore della risoluzioned
            $solverid = $submission_data->authorid;

            /* La variabile submission_data contiene la tripla
             submission - studente valutatore - studente risolutore con, in più,
            anche i dati della valutazione eseguita dal valutatore */

            // Recupero del punteggio della submission al nuovo istante t0, se non è presente perchè quella in oggetto è la prima valutazione è zero.
            // Stesso procedimento per la steadiness della submission

            $mpa_data = $DB->get_records_sql('SELECT * FROM {mpa_submission_data} WHERE id=? AND evaluatorid=? AND solverid=?', array($submissionid,$evaluatorid,$solverid));
            $temp = array_pop($mpa_data);

            if(empty($mpda_data)){
                $old_submission_steadiness = 0;
                $old_submission_score = 0;
            } else {
                $old_submission_steadiness = $temp->submission_steadiness/MULTIPLIER;
                $old_submission_score = $temp->submission_score/MULTIPLIER;
            }

            // Recupero dei punteggi valutatore e risolutore al nuovo istante t0, se non sono presenti perchè quella in oggetto è la prima valutazione, il primo viene inizializzato con una quantità epsilon arbitrariamente piccola, mentre il secondo è posto a zero.

            $mpa_data = $DB->get_records_sql('SELECT * FROM {mpa_submission_data} WHERE evaluatorid=?', array($evaluatorid));
            $temp = array_pop($mpa_data);

            if(empty($mpda_data)){
                $old_evaluator_score = EPSILON;
                $old_solver_score = 0;
            } else {
                $old_evaluator_score = $temp->evaluator_score/MULTIPLIER;
                $old_solver_score = $temp->solver_score/MULTIPLIER;
            }

            // Recupero del giudizio espresso dal valutatore

            $assessment_value = $submission_data->grade;

            // Recupero del livello di confidenza per l'assessment in oggetto. Se il valutatore non ha espresso tale valore, viene usato un valore molto piccolo epsilon.

            $mpa_data = $DB->get_records_sql('SELECT * FROM {mpa_confidence_levels} WHERE id=? AND evaluatorid=?', array($submissionid,$evaluatorid));
            $temp = array_pop($mpa_data);

            if($temp->confidence_level==0){
                $confidence_level=EPSILON;
            } else {
                $confidence_level=$temp->confidence_level/MULTIPLIER;
            }

            // Adesso che i valori precedenti sono noti, scatta l'aggiornamento del punteggio della submission, del punteggio del valutatore e di quello del risolutore all'istante t1.

            // Aggiornamento della steadiness della submission all'istante di tempo t1

            $submission_steadiness = $old_submission_steadiness + ($old_evaluator_score * $confidence_level);

            echo '<pre>';
            print_r($submission_steadiness);
            echo '</pre>';

            // Aggiornamento per punteggio della submission all'istante di tempo t1

            $submission_score = (($old_submission_steadiness * $old_submission_score) + ($old_evaluator_score * $assessment_value * $confidence_level)) / $submission_steadiness;

        }

        echo $renderer->render_student_scores();

    } else {
        echo $renderer->render_capability_error();
    }

} else {

    print_page_attributes('pluginname', 'pluginname', 0, 'local');
    $renderer = $PAGE->get_renderer('local_mpa');
    echo $renderer->render_login_required();

}