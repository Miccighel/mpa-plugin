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

            $temp = $DB->get_records_sql('SELECT * FROM {mpa_submission_data} WHERE id=? AND evaluatorid=? AND solverid=?', array($submissionid,$evaluatorid,$solverid));

            if (empty($temp)) {
                $DB->execute('INSERT INTO {mpa_submission_data} (id,evaluatorid,solverid,submission_steadiness,submission_score,assessment_value,assessment_goodness)
                    VALUES (?,?,?,?,?,?,?)',$parms=array($submissionid,$evaluatorid,$solverid,0.000000,0.000000,0.0000000,0.0000000));
            }

            $temp = $DB->get_records_sql('SELECT * FROM {mpa_student_scores} WHERE id=?', array($solverid));

            if(empty($temp)){
                $DB->execute('INSERT INTO {mpa_student_scores} (id,solver_score,solver_steadiness) VALUES(?,?,?)',array($solverid,0.000000,0.000000));
            }

            $temp = $DB->get_records_sql('SELECT * FROM {mpa_student_scores} WHERE id=?', array($evaluatorid));

            if(empty($temp)){
                $DB->execute('INSERT INTO {mpa_student_scores} (id,evaluator_score,evaluator_steadiness) VALUES(?,?,?)',array($evaluatorid,0.000000,0.000000));
            }

            // Per la submission correntemente analizzata, vengono estratte le proprietà, gli assessment ed anche i voti parziali degli assessment al fine di calcolare i punteggi del risolutore e quelli del valutatore.

            $temp = $DB->get_records_sql('SELECT * FROM {workshop_submissions} WHERE id=?', array($submissionid));

            $current_submission = new Submission($submission_data);
            $current_submission_properties = $current_submission->getProperties();
            $current_submission->loadAssessments();
            $current_assessments = $current_submission->getAssessments();

            // Dati già presenti nelle tabelle del database per la submission correntemente analizzata

            $current_submission_data = $DB->get_records_sql('SELECT * FROM {mpa_submission_data} WHERE id=? AND evaluatorid=? AND solverid=?', array($submissionid,$evaluatorid,$solverid));
            $current_evaluator_data = $DB->get_records_sql('SELECT * FROM {mpa_student_scores} WHERE id=?',array($evaluatorid));
            $current_solver_data = $DB->get_records_sql('SELECT * FROM {mpa_student_scores} WHERE id=?',array($solverid));

            foreach($current_assessments as $current_assessment){

                $current_assessment_properties = $current_assessment->getProperties();

                $confidence_level = array_pop($DB->get_records_sql('SELECT confidence_level FROM {mpa_submission_data} WHERE id=? AND evaluatorid=?', array($current_submission_properties->id,$current_assessment_properties->reviewerid)))->confidence_level/100;

            }

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