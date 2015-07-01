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

    if (has_capability('local/mpa:localoverview', $usercontext, $userid)) {

        $renderer = $PAGE->get_renderer('local_mpa');

        define('threshold_assessments',1);

        $final_students = get_active_students();

        foreach ($final_students as $student) {
            $stud_prop = $student->getProperties();
            $submissions = $student->getSubmissions();
            foreach($submissions as $submission) {
                $sub_prop = $submission->getProperties();
                $assessments = $submission->getAssessments();
                foreach($assessments as $assesment){
                    $ass_prop = $assesment->getProperties();
                    $grades = $assesment->getGrades();
                    foreach($grades as $grade){
                        $grade_prop = $grade->getProperties();
                    }
                }
            }
        }

        echo $renderer->render_student_scores();

    } else {
        echo $renderer->render_capability_error();
    }

} else {

    print_page_attributes('pluginname', 'pluginname', null, 'local');
    $renderer = $PAGE->get_renderer('local_mpa');
    echo $renderer->render_login_required();

}