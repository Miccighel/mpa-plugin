<?php

/**
 * Pagina di visualizzazione del feedback ricevuto sugli esercizi svolti nei workshop ricevuti da altri studenti da quello correntemente collegato.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

require(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/local/mpa/locallib.php');
require_once($CFG->dirroot . '/local/mpa/classes/student.php');

$userid = $USER->id;
$usercontext = context_user::instance($userid);
print_page_attributes('pluginname', 'pluginname', $usercontext, 'local');

$renderer = $PAGE->get_renderer('local_mpa');

$submissions = $DB->get_records_sql('SELECT w.name,w.intro,w.instructauthors,w.instructreviewers,w.conclusion,mws.id,authorid,title,content FROM ({workshop_submissions} AS mws INNER JOIN {user} AS mu ON mws.authorid=mu.id) INNER JOIN {workshop} AS w ON mws.workshopid=w.id WHERE mws.authorid=?',array($userid));

$studentHandler = new Student($DB->get_records_sql('SELECT id,username,lastname,email FROM {user} WHERE id=?',array($userid)));

foreach($submissions as $object){
    $submission = new Submission($object);
    $assessments = $DB->get_records_sql('SELECT id,submissionid,feedbackauthor,grade FROM {workshop_assessments} WHERE submissionid=?',array($object->id));
    foreach ($assessments as $object){
        $assessment = new Assessment($object);
        $grades = $DB->get_records_sql('SELECT wa.description,wg.id, wg.peercomment, wg.grade FROM {workshop_grades} AS wg INNER JOIN {workshopform_accumulative} AS wa ON wg.dimensionid=wa.id WHERE assessmentid=?',array($object->id));
        foreach($grades as $object) {
            $grade = new Grade($object);
            $assessment->addGrade($grade);
        }
        $submission->addAssessment($assessment);
    }
    $studentHandler->addSubmission($submission);
}

echo $renderer->render_student_feedback($studentHandler);