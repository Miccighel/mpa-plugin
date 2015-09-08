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

if (isloggedin()) {

    $userid = $USER->id;
    $usercontext = context_user::instance($userid);
    print_page_attributes('pluginname', 'pluginname', $usercontext, 'local');

    $renderer = $PAGE->get_renderer('local_mpa');

    if (has_capability('local/mpa:studentscore', $usercontext, $userid)) {

        $submissions_data = $DB->get_records_sql('SELECT *
        FROM {workshop_submissions} AS mws INNER JOIN {workshop_assessments} AS mwa ON mwa.submissionid=mws.id WHERE mwa.reviewerid!=mws.authorid', array());

        $configuration = $DB->get_records_sql('SELECT * FROM {mpa_configuration_info}');

        if (empty($configuration)) {

            echo $renderer->render_student_scores(null);

        } else {

            $configuration = array_pop($configuration);

            define('EPSILON', $configuration->epsilon);
            define('GRADING_FACTOR', $configuration->grading_factor);
            define('INFINITY', $configuration->infinity);
            define('TEACHER_WEIGHT', $configuration->teacher_weight);

            foreach ($submissions_data as $submission_data) {

                // ID della risoluzione
                $submissionid = $submission_data->submissionid;
                // Autore dell'assessment per la risoluzione
                $evaluatorid = $submission_data->reviewerid;
                // Autore della risoluzioned
                $solverid = $submission_data->authorid;

                // Verifica dello status di docente del valutatore per il corso corrente

                $evaluator = new Student($evaluatorid);
                $current_course_id = array_pop($DB->get_records_sql("SELECT mc.id FROM (({workshop_aggregations} AS mwa INNER JOIN {workshop} AS mw ON mwa.workshopid=mw.id) INNER JOIN {course} AS mc ON mw.course=mc.id) WHERE mwa.userid=?", array($evaluatorid)))->id;
                $is_teacher = $evaluator->isTeacher($current_course_id);

                /* La variabile submission_data contiene la tripla
                 submission - studente valutatore - studente risolutore con, in più,
                anche i dati della valutazione eseguita dal valutatore */

                // Recupero del punteggio della submission al nuovo istante ti, se non è presente perchè quella in oggetto è la prima valutazione è zero.
                // Stesso procedimento per la steadiness della submission

                $mpa_data = $DB->get_records_sql('SELECT * FROM {mpa_submission_data} WHERE id=? AND evaluatorid=? AND solverid=?', array($submissionid, $evaluatorid, $solverid));

                if (empty($mpda_data)) {
                    $old_submission_steadiness = 0;
                    $old_submission_score = 0;
                    $old_assessment_goodness = 0;
                } else {
                    $temp = array_pop($mpa_data);
                    $old_submission_steadiness = $temp->submission_steadiness;
                    $old_submission_score = $temp->submission_score;
                    $old_assessment_goodness = $temp->assessment_goodness;
                }

                // Recupero dei punteggi valutatore e risolutore (più relative stabilità) al nuovo istante ti, se non sono presenti perchè quella in oggetto è la prima valutazione, il primo viene inizializzato con una quantità epsilon arbitrariamente piccola, mentre il secondo è posto a zero.

                $mpa_data = $DB->get_records_sql('SELECT * FROM {mpa_submission_data} WHERE evaluatorid=? OR solverid=?', array($evaluatorid, $solverid));

                if (empty($mpda_data)) {
                    $old_evaluator_score = EPSILON;
                    $old_solver_score = 0;
                    $old_solver_steadiness = 0;
                    $old_evaluator_steadiness = 0;
                } else {
                    $temp = array_pop($mpa_data);
                    $old_evaluator_score = $temp->evaluator_score;
                    $old_solver_score = $temp->solver_score;
                    $old_evaluator_steadiness = $temp->evaluator_steadiness;
                    $old_solver_steadiness = $temp->solver_steadiness;
                }

                // Recupero del giudizio espresso dal valutatore

                $assessment_value = $submission_data->grade / GRADING_FACTOR;

                // Recupero del livello di confidenza per l'assessment in oggetto. Se il valutatore non ha espresso tale valore, viene usato un valore molto piccolo epsilon.

                $mpa_data = $DB->get_records_sql('SELECT * FROM {mpa_confidence_levels} WHERE id=? AND evaluatorid=?', array($submissionid, $evaluatorid));
                $temp = array_pop($mpa_data);

                if ($temp->confidence_level == 0) {
                    $confidence_level = EPSILON;
                } else {
                    $confidence_level = $temp->confidence_level;
                }

                // Se il valutatore corrente è un insegnante il suo punteggio tende all'infinito e il livello di confidenza è automaticamente uno.

                if ($is_teacher) {
                    $old_evaluator_score = INFINITY;
                    $old_evaluator_steadiness = INFINITY;
                    $confidence_level = 1;
                }

                // Adesso che i valori precedenti sono noti, scatta l'aggiornamento del punteggio della submission, del punteggio del valutatore e di quello del risolutore all'istante ti+1.

                // Aggiornamento della steadiness della submission all'istante di tempo ti+1

                $submission_steadiness = $old_submission_steadiness + ($old_evaluator_score * $confidence_level);

                // Aggiornamento del punteggio della submission all'istante di tempo ti+1

                $submission_score = (($old_submission_steadiness * $old_submission_score) + ($old_evaluator_score * $assessment_value * $confidence_level)) / $submission_steadiness;

                // Applicazione della funzione f sulla stabilità della submission agli istanti di tempo ti e ti+1

                $function_value_old_submission_steadiness;

                if ($old_submission_steadiness <= TEACHER_WEIGHT) {
                    // Il valore rimane invariato
                    $function_value_old_submission_steadiness = $old_submission_steadiness;
                } else {
                    $function_value_old_submission_steadiness = TEACHER_WEIGHT;
                }

                $function_value_submission_steadiness;

                if ($submission_steadiness <= TEACHER_WEIGHT) {
                    // Il valore rimane invariato
                    $function_value_submission_steadiness = $submission_steadiness;
                } else {
                    $function_value_submission_steadiness = TEACHER_WEIGHT;
                }

                // Aggiornamento della stabilità dello studente risolutore correntemente analizzato all'istante di tempo ti+1

                $solver_steadiness = $old_solver_steadiness - $function_value_old_submission_steadiness + $function_value_submission_steadiness;

                // Aggiornamento del punteggio dello studente risolutore correntemente analizzato all'istante di tempo ti+1

                $solver_score = (($old_solver_steadiness * $old_solver_score) - ($function_value_old_submission_steadiness * $old_submission_score) + ($function_value_submission_steadiness * $submission_score)) / $solver_steadiness;

                // Calcolo della bontà del giudizio correntemente analizzato espresso sulla submission dallo studente valutatore all'istante di tempo ti+1

                $assessment_goodness = 1 - (sqrt(abs($assessment_value - $submission_score)));

                // Calcolo della stabilità dello studente valutatore correntemente analizzato all'istante di tempo ti+1

                $evaluator_steadiness = $old_evaluator_steadiness + ($submission_steadiness * $confidence_level);

                // Calcolo del punteggio dello studente valutatore correntemente analizzato all'istante di tempo ti+1

                $evaluator_score = (($old_evaluator_steadiness * $old_evaluator_score) + ($submission_steadiness * $assessment_goodness * $confidence_level)) / $evaluator_steadiness;

                // Aggiornamento di stabilità e punteggio per i precedenti studenti valutatori. Il tempo che li caratterizza è dopo t0 e prima di ti+1.

                $mpa_data = $DB->get_records_sql('SELECT mss.id AS studentid,msd.id AS submissionid,msd.evaluatorid,msd.solverid,msd.submission_steadiness,msd.submission_score,mss.evaluator_steadiness,mss.evaluator_score FROM {mpa_submission_data} AS msd INNER JOIN {mpa_student_scores} AS mss ON msd.evaluatorid=mss.id WHERE msd.id=? AND msd.evaluatorid!=? AND msd.solverid', array($submissionid, $evaluatorid, $solverid));

                foreach ($mpa_data as $previous_evaluator) {

                    $previous_evaluator_steadiness = $previous_evaluator->evaluator_steadiness;
                    $previous_evaluator_score = $previous_evaluator->evaluator_score;
                    $previous_assessment_goodness = $previous_evaluator->assessment_goodness;
                    $previous_assessment_value = array_pop($DB->get_records_sql('SELECT grade FROM {workshop_assessments} WHERE submissionid=? AND reviewerid=?', array($submissionid, $previous_evaluator->evaluatorid)))->grade / GRADING_FACTOR;
                    $previous_confidence_level = array_pop($DB->get_records_sql('SELECT confidence_level FROM {mpa_confidence_levels} WHERE id=? AND evaluatorid=?', array($submissionid, $previous_evaluator->evaluatorid)))->confidence_level;
                    $new_evaluator_steadiness = $previous_evaluator_steadiness + ($old_evaluator_score * $confidence_level * $previous_confidence_level);
                    $new_assessment_goodness = 1 - sqrt(abs($previous_assessment_value - $submission_score));
                    $new_evaluator_score = (($previous_evaluator_steadiness * $previous_evaluator_score) - ($old_submission_steadiness * $previous_assessment_goodness * $previous_confidence_level) + ($submission_steadiness * $new_assessment_goodness * $previous_confidence_level)) / $new_evaluator_steadiness;

                    $mpa_data = $DB->execute('UPDATE {mpa_student_scores} SET evaluator_score=?, evaluator_steadiness=? WHERE id=?', array($new_evaluator_score, $new_evaluator_steadiness, $previous_evaluator->evaluatorid));
                    $mpa_data = $DB->execute('UPDATE {mpa_submission_data} SET assessment_goodness=? WHERE id=? AND evaluatorid=?', array($new_assessment_goodness, $submissionid, $previous_evaluator->evaluatorid));

                }

                // I valori calcolati per la tripla correntemente analizzata vengono inseriti nella base di dati

                $mpa_data = $DB->get_records_sql('SELECT * FROM {mpa_submission_data} WHERE id=? AND evaluatorid=? AND solverid=?', array($submissionid, $evaluatorid, $solverid));

                if (empty($mpa_data)) {
                    $DB->execute('INSERT INTO {mpa_submission_data} (id,evaluatorid,solverid,submission_steadiness,submission_score,assessment_goodness) VALUES
                              (?,?,?,?,?,?)', array($submissionid, $evaluatorid, $solverid, $submission_steadiness, $submission_score, $assessment_goodness));
                } else {
                    $DB->execute('UPDATE {mpa_submission_data} SET submission_steadiness=?, submission_score=?, assessment_goodness=? WHERE id=? AND evaluatorid=? AND solverid=?',
                        array($submission_steadiness, $submission_score, $assessment_goodness, $submissionid, $evaluatorid, $solverid));
                }

                $mpa_data = $DB->get_records_sql('SELECT * FROM {mpa_student_scores} WHERE id=?', array($evaluatorid));

                if (empty($mpa_data)) {
                    $DB->execute('INSERT INTO {mpa_student_scores} (id,evaluator_score,evaluator_steadiness) VALUES
                              (?,?,?)', array($evaluatorid, $evaluator_score, $evaluator_steadiness));
                } else {
                    $DB->execute('UPDATE {mpa_student_scores} SET evaluator_steadiness=?, evaluator_score=? WHERE id=?',
                        array($evaluator_steadiness, $evaluator_steadiness, $evaluatorid));
                }

                $mpa_data = $DB->get_records_sql('SELECT * FROM {mpa_student_scores} WHERE id=?', array($solverid));

                if (empty($mpa_data)) {
                    $DB->execute('INSERT INTO {mpa_student_scores} (id,solver_score,solver_steadiness) VALUES
                              (?,?,?)', array($solverid, $solver_score, $solver_steadiness));
                } else {
                    $DB->execute('UPDATE {mpa_student_scores} SET solver_steadiness=?, solver_score=? WHERE id=?',
                        array($solver_steadiness, $solver_steadiness, $solverid));
                }

            }


            $students_data = $DB->get_records_sql('SELECT * FROM {user} AS mu INNER JOIN {mpa_student_scores} AS mss ON mu.id=mss.id');

            echo $renderer->render_student_scores($students_data);
        }

    } else {
        echo $renderer->render_capability_error();
    }

} else {

    print_page_attributes('pluginname', 'pluginname', 0, 'local');
    $renderer = $PAGE->get_renderer('local_mpa');
    echo $renderer->render_login_required();

}