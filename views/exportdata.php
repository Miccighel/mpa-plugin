<?php

/*
 * Pagina per l'esportazione dei dati in formato csv.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

require(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/local/mpa/locallib.php');

if (isloggedin()) {

    $userid = $USER->id;
    $usercontext = context_user::instance($userid);

    print_page_attributes('pluginname', 'pluginname', $usercontext, 'local');

    $renderer = $PAGE->get_renderer('local_mpa');

    if (has_capability('local/mpa:exportdata', $usercontext, $userid)) {


        $data = $DB->get_records_sql('SELECT mu.id AS student_id,mu.firstname,mu.lastname,mu.username,mss.solver_score,
                                             mss.solver_steadiness,mss.evaluator_score,mss.evaluator_steadiness,
                                             msum.ex_to_evaluate_solved,msum.ex_assessed,msum.assigned_grades,
                                             msum.received_grades,msum.assignments_solved,msd.id AS submission_id,msd.evaluatorid AS evaluator_id,
                                             msd.solverid AS solver_id,msd.submission_steadiness,msd.submission_score,msd.assessment_goodness,
                                             mcl.confidence_level
                                      FROM (((({user} AS mu INNER JOIN {mpa_student_scores} AS mss ON mu.id=mss.id)
                                      INNER JOIN {mpa_student_summary} AS msum ON mss.id=msum.id)
                                      INNER JOIN {mpa_submission_data} AS msd ON msum.id=msd.evaluatorid)
                                      INNER JOIN {mpa_confidence_levels} AS mcl ON msd.evaluatorid = mcl.evaluatorid AND msd.id = mcl.id)');

        $matrix = array();
        foreach ($data as $temp) {
            $row = array();
            $row['student_id'] = $temp->student_id;
            $row['firstname'] = $temp->firstname;
            $row['lastname'] = $temp->lastname;
            $row['username'] = $temp->username;
            $row['solver_score'] = $temp->solver_score;
            $row['solver_steadiness'] = $temp->solver_steadiness;
            $row['evaluator_score'] = $temp->evaluator_score;
            $row['evaluator_steadiness'] = $temp->evaluator_steadiness;
            $row['ex_to_evaluate_solved'] = $temp->ex_to_evaluate_solved;
            $row['ex_assessed'] = $temp->ex_assessed;
            $row['assigned_grades'] = $temp->assigned_grades;
            $row['received_grades'] = $temp->received_grades;
            $row['assignments_solved'] = $temp->assignments_solved;
            $row['submission_id'] = $temp->submission_id;
            $row['evaluator_id'] = $temp->evaluator_id;
            $row['solver_id'] = $temp->solver_id;
            $row['submission_steadiness'] = $temp->submission_steadiness;
            $row['submission_score'] = $temp->submission_score;
            $row['assessment_goodness'] = $temp->assessment_goodness;
            array_push($matrix, $row);
        }

        download_send_headers("data_export_" . date("Y-m-d") . ".csv");
        echo array2csv($matrix);
        die();

    } else {
        echo $renderer->render_capability_error();
    }

} else {

    print_page_attributes('pluginname', 'pluginname', null, 'local');
    $renderer = $PAGE->get_renderer('local_mpa');
    echo $renderer->render_login_required();

}