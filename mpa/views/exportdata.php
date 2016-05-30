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


        $data_evaluators = $DB->get_records_sql('SELECT mu.id AS student_id,mu.firstname,mu.lastname,mu.username,mss.solver_score,
                                             mss.solver_steadiness,mss.evaluator_score,mss.evaluator_steadiness,
                                             msum.ex_to_evaluate_solved,msum.ex_assessed,msum.assigned_grades,
                                             msum.received_grades,msum.assignments_solved,msd.id AS submission_id,msd.evaluatorid AS evaluator_id,
                                             msd.solverid AS solver_id,msd.submission_steadiness,msd.submission_score,msd.assessment_goodness,
                                             mcl.confidence_level
                                      FROM (((({user} AS mu INNER JOIN {mpa_student_scores} AS mss ON mu.id=mss.id)
                                      INNER JOIN {mpa_student_summary} AS msum ON mss.id=msum.id)
                                      INNER JOIN {mpa_submission_data} AS msd ON msum.id=msd.evaluatorid)
                                      INNER JOIN {mpa_confidence_levels} AS mcl ON msd.evaluatorid = mcl.evaluatorid AND msd.id = mcl.id)');

        $matrix_evaluators = array();
        foreach ($data_evaluators as $temp) {
            $row = array(
                $temp->student_id,
                $temp->firstname,
                $temp->lastname,
                $temp->username,
                $temp->assignments_solved,
                $temp->ex_to_evaluate_solved,
                $temp->ex_assessed,
                $temp->assigned_grades,
                $temp->received_grades,
                $temp->solver_score,
                $temp->evaluator_score,
                $temp->confidence_level,
                $temp->solver_steadiness,
                $temp->evaluator_steadiness,
                $temp->submission_steadiness,
                $temp->submission_score,
                $temp->assessment_goodness,
                $temp->submission_id,
                $temp->evaluator_id,
                $temp->solver_id,
            );
            array_push($matrix_evaluators, $row);
        }

        $file_evaluators_name = "../data/evaluators_data.csv";

        $file_evaluators_data = fopen($file_evaluators_name,"w");
        $header = array(
            get_string('student_id', 'local_mpa'),
            get_string('firstname', 'local_mpa'),
            get_string('lastname', 'local_mpa'),
            get_string('username', 'local_mpa'),
            get_string('assignments_solved', 'local_mpa'),
            get_string('ex_to_evaluate_solved', 'local_mpa'),
            get_string('ex_assessed', 'local_mpa'),
            get_string('assigned_grades', 'local_mpa'),
            get_string('received_grades', 'local_mpa'),
            get_string('solver_score', 'local_mpa'),
            get_string('evaluator_score', 'local_mpa'),
            get_string('confidence_level', 'local_mpa'),
            get_string('solver_steadiness', 'local_mpa'),
            get_string('evaluator_steadiness', 'local_mpa'),
            get_string('submission_steadiness', 'local_mpa'),
            get_string('submission_score', 'local_mpa'),
            get_string('assessment_goodness', 'local_mpa'),
            get_string('submission_id', 'local_mpa'),
            get_string('evaluator_id', 'local_mpa'),
            get_string('solver_id', 'local_mpa'),
        );
        fputcsv($file_evaluators_data, $header);
        foreach ($matrix_evaluators as $evaluator) {
            fputcsv($file_evaluators_data, $evaluator);
        }
        fclose($file_evaluators_data_data);

        $data_solvers = $DB->get_records_sql('SELECT mu.id AS student_id,mu.firstname,mu.lastname,mu.username,
                                             msum.ex_to_evaluate_solved,msum.ex_assessed,msum.assigned_grades,
                                             msum.received_grades,msum.assignments_solved
                                      FROM ({user} AS mu INNER JOIN {mpa_student_summary} AS msum ON mu.id=msum.id)
                                      WHERE msum.ex_to_evaluate_solved=0');

        $matrix_solvers = array();
        foreach ($data_solvers as $temp) {
            $row = array(
                $temp->student_id,
                $temp->firstname,
                $temp->lastname,
                $temp->username,
                $temp->assignments_solved,
                $temp->ex_to_evaluate_solved,
                $temp->ex_assessed,
                $temp->assigned_grades,
                $temp->received_grades,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
            );
            array_push($matrix_solvers, $row);
        }
        
        $file_solvers_name = "../data/solvers_data.csv";

        $file_solvers_data = fopen($file_solvers_name,"w");
        $header = array(
            get_string('student_id', 'local_mpa'),
            get_string('firstname', 'local_mpa'),
            get_string('lastname', 'local_mpa'),
            get_string('username', 'local_mpa'),
            get_string('assignments_solved', 'local_mpa'),
            get_string('ex_to_evaluate_solved', 'local_mpa'),
            get_string('ex_assessed', 'local_mpa'),
            get_string('assigned_grades', 'local_mpa'),
            get_string('received_grades', 'local_mpa'),
            get_string('solver_score', 'local_mpa'),
            get_string('evaluator_score', 'local_mpa'),
            get_string('confidence_level', 'local_mpa'),
            get_string('solver_steadiness', 'local_mpa'),
            get_string('evaluator_steadiness', 'local_mpa'),
            get_string('submission_steadiness', 'local_mpa'),
            get_string('submission_score', 'local_mpa'),
            get_string('assessment_goodness', 'local_mpa'),
            get_string('submission_id', 'local_mpa'),
            get_string('evaluator_id', 'local_mpa'),
            get_string('solver_id', 'local_mpa'),
        );
        fputcsv($file_solvers_data, $header);
        foreach ($matrix_solvers as $solver) {
            fputcsv($file_solvers_data, $solver);
        }
        fclose($file_solvers_data);

        $files = array($file_evaluators_name,$file_solvers_name);

        $zip_name= 'data_export.zip';
        $zip = new ZipArchive;
        $zip->open($zip_name, ZipArchive::CREATE);
        foreach ($files as $file) {
            $zip->addFile($file);
        }
        $zip->close();

        download_send_headers($zip_name);

    } else {
        echo $renderer->render_capability_error();
    }

} else {

    print_page_attributes('pluginname', 'pluginname', null, 'local');
    $renderer = $PAGE->get_renderer('local_mpa');
    echo $renderer->render_login_required();

}