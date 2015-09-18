<?php
/**
 * Classe contenente le viste del plugin
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mpa/locallib.php');
require_once($CFG->dirroot . '/local/mpa/classes/form.php');

class local_mpa_renderer extends plugin_renderer_base
{

    public function render_student_summary($students_data)
    {

        global $OUTPUT;

        echo $OUTPUT->header();

        $table = new html_table();
        $table->head = array(get_string('username', 'local_mpa'), get_string('ex_to_evaluate_solved', 'local_mpa'), get_string('ex_assessed', 'local_mpa'), get_string('assignments_solved', 'local_mpa'), get_string('assigned_grades', 'local_mpa'), get_string('received_grades', 'local_mpa'));
        foreach ($students_data as $student) {
            $info = $student->getProperties();
            $table->data[] = array($info->username, $student->getExToEvaluateSolved(), $student->getExAssessed(), $student->getAssignmentsSolved(), $student->getAssignedGrades(), $student->getReceivedGrades());
        }

        echo html_writer::table($table);

        echo $OUTPUT->footer();
    }

    public function render_student_feedback($logged_student)
    {

        global $OUTPUT;

        echo $OUTPUT->header();

        $submissions = $logged_student->getSubmissions();

        foreach ($submissions as $submission) {

            $subProperties = $submission->getProperties();
            $assessments = $submission->getAssessments();

            echo html_writer::tag('h2', get_string('submission', 'local_mpa'));

            echo html_writer::start_tag('table', array('class' => 'generaltable'));
            echo html_writer::start_tag('thead');
            echo html_writer::start_tag('tr');
            echo html_writer::tag('th', get_string('workname', 'local_mpa'), array('class' => 'header'));
            echo html_writer::tag('th', get_string('workintroduction', 'local_mpa'), array('class' => 'header'));
            echo html_writer::tag('th', get_string('instructauthors', 'local_mpa'), array('class' => 'header'));
            //echo html_writer::tag('th', get_string('instructreviewers', 'local_mpa'), array('class' => 'header'));
            echo html_writer::tag('th', get_string('responsetitle', 'local_mpa'), array('class' => 'header'));
            echo html_writer::tag('th', get_string('responsecontent', 'local_mpa'), array('class' => 'header'));
            echo html_writer::end_tag('tr');
            echo html_writer::end_tag('thead');
            echo html_writer::start_tag('tr');
            echo html_writer::tag('td', $subProperties->name);
            echo html_writer::tag('td', $subProperties->intro);
            echo html_writer::tag('td', $subProperties->instructauthors);
            //echo html_writer::tag('td', $subProperties->instructreviewers);
            echo html_writer::tag('td', $subProperties->title);
            echo html_writer::tag('td', $subProperties->content);
            echo html_writer::end_tag('tr');
            echo html_writer::end_tag('table');

            foreach ($assessments as $assessment) {

                echo html_writer::tag('h3', get_string('assessment', 'local_mpa'));

                $assProperties = $assessment->getProperties();
                $grades = $assessment->getGrades();

                echo html_writer::start_tag('table', array('class' => 'generaltable'));
                echo html_writer::start_tag('thead');
                echo html_writer::start_tag('tr');
                echo html_writer::tag('th', get_string('assgrade', 'local_mpa'), array('class' => 'header'));
                echo html_writer::tag('th', get_string('assfeedbackauthor', 'local_mpa'), array('class' => 'header'));
                echo html_writer::end_tag('tr');
                echo html_writer::end_tag('thead');
                echo html_writer::start_tag('tr');
                echo html_writer::tag('td', $assProperties->grade);
                echo html_writer::tag('td', $assProperties->feedbackauthor);
                echo html_writer::end_tag('tr');
                echo html_writer::end_tag('table');

                echo html_writer::tag('h4', get_string('grades', 'local_mpa'));

                echo html_writer::start_tag('table', array('class' => 'generaltable'));
                echo html_writer::start_tag('tr');
                echo html_writer::tag('th', get_string('gradedescription', 'local_mpa'), array('class' => 'header'));
                echo html_writer::tag('th', get_string('grade', 'local_mpa'), array('class' => 'header'));
                echo html_writer::tag('th', get_string('peercomment', 'local_mpa'), array('class' => 'header'));
                echo html_writer::end_tag('tr');

                foreach ($grades as $grade) {

                    $gradProperties = $grade->getProperties();

                    echo html_writer::start_tag('tr');
                    echo html_writer::tag('td', $gradProperties->description);
                    echo html_writer::tag('td', $gradProperties->grade);
                    echo html_writer::tag('td', $gradProperties->peercomment);
                    echo html_writer::end_tag('tr');

                }
                echo html_writer::end_tag('table');

            }

            echo '____________________________________________________________________________________________________________________________________________________';

        }

        echo $OUTPUT->footer();
    }

    public function render_student_scores($students_data)
    {

        global $OUTPUT;

        echo $OUTPUT->header();

        $table = new html_table();
        $table->head = array(get_string('username', 'local_mpa'), get_string('solver_score', 'local_mpa'), get_string('evaluator_score', 'local_mpa'), get_string('solver_steadiness', 'local_mpa'), get_string('evaluator_steadiness', 'local_mpa'));

        foreach ($students_data as $student) {
            $table->data[] = array($student->username, $student->solver_score, $student->evaluator_score, $student->solver_steadiness, $student->evaluator_steadiness);
        }
        echo html_writer::table($table);


        echo $OUTPUT->footer();
    }

    public function render_capability_error()
    {

        global $OUTPUT;

        echo $OUTPUT->header();

        echo get_string('capabilityerror', 'local_mpa');

        echo $OUTPUT->footer();
    }

    public function render_local_overview()
    {
        global $OUTPUT, $DB;

        echo $OUTPUT->header();

        echo get_string('configurationinfo', 'local_mpa');

        $form = new configuration_form(null, null);

        if ($form->is_cancelled()) {
        } else if ($data = $form->get_data()) {
            $temp = $DB->get_records_sql('SELECT * FROM {mpa_configuration_info}');
            $epsilon = $data->epsilon;
            if ($epsilon == null) {
                $epsilon = 0.01;
            }
            $infinity = $data->infinity;
            if ($infinity == null) {
                $infinity = 1000000;
            }
            $gradingfactor = $data->gradingfactor;
            if ($gradingfactor == null) {
                $gradingfactor = 100;
            }
            $teacherweight = $data->teacherweight;
            if ($teacherweight == null) {
                $teacherweight = 0.5;
            }
            if (empty($temp)) {
                $DB->execute('INSERT INTO {mpa_configuration_info} (epsilon,infinity,grading_factor,teacher_weight) VALUES (?,?,?,?)', array($epsilon, $infinity, $gradingfactor, $teacherweight));
            } else {
                $old_conf = array_pop($temp);
                $DB->execute('UPDATE {mpa_configuration_info} SET epsilon=?,infinity=?,grading_factor=?,teacher_weight=?
                              WHERE epsilon=? AND infinity=? AND grading_factor=? AND teacher_weight=?',
                    array($epsilon, $infinity, $gradingfactor, $teacherweight, $old_conf->epsilon, $old_conf->infinity, $old_conf->grading_factor, $old_conf->teacher_weight));
            }

            $urltogo = new moodle_url(($CFG->wwwroot . '/local/mpa/index.php'), null, null, null);
            redirect($urltogo);

        } else {
            $form->display();
        }

        echo $OUTPUT->footer();
    }

    public function render_confidence_assignment($given_assessments, $logged_student)
    {

        global $OUTPUT, $DB;

        echo $OUTPUT->header();

        echo html_writer::tag('h2', get_string('confidencelevelassignment', 'local_mpa'));

        echo html_writer::start_tag('table', array('class' => 'generaltable'));
        echo html_writer::start_tag('thead');
        echo html_writer::start_tag('tr');
        echo html_writer::tag('th', get_string('workname', 'local_mpa'), array('class' => 'header'));
        echo html_writer::tag('th', get_string('submission', 'local_mpa'), array('class' => 'header'));
        echo html_writer::tag('th', get_string('grade', 'local_mpa'), array('class' => 'header'));
        echo html_writer::tag('th', get_string('assfeedbackauthor', 'local_mpa'), array('class' => 'header'));
        echo html_writer::tag('th', '', array('class' => 'header'));
        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('thead');

        foreach ($given_assessments as $assessment) {
            $properties = $assessment->getProperties();
            echo html_writer::start_tag('tr');
            echo html_writer::tag('td', $properties->name);
            echo html_writer::tag('td', $properties->content);
            echo html_writer::tag('td', $properties->grade);
            echo html_writer::tag('td', $properties->feedbackauthor);
            echo html_writer::start_tag('td');
            echo html_writer::tag('h5', get_string('actuallevel', 'local_mpa') . " " . $properties->confidence_level * 100);
            echo html_writer::end_tag('td');
            echo html_writer::end_tag('tr');
        }

        echo html_writer::end_tag('table');

        $form = new confidence_form(null, array('items' => sizeof($given_assessments)));

        if ($form->is_cancelled()) {
        } else if ($data = $form->get_data()) {
            for ($i = 0; $i < sizeof($given_assessments); $i++) {
                $identifier = 'level' . $i;
                //echo $data->$identifier;

                $student_id = $logged_student->id;
                $properties = $given_assessments[$i]->getProperties();

                $temp = $DB->get_records_sql('SELECT confidence_level FROM {mpa_confidence_levels} WHERE evaluatorid=? AND id=?', array($student_id, $properties->id));

                if (empty($temp)) {
                    if ($data->$identifier != get_string('notset', 'local_mpa')) {
                        $DB->execute('INSERT INTO {mpa_confidence_levels} (id,evaluatorid,confidence_level) VALUES (?,?,?)', $parms = array($properties->id, $student_id, $data->$identifier / 100));
                    }
                } else {
                    if ($data->$identifier != get_string('notset', 'local_mpa')) {
                        $DB->execute('UPDATE {mpa_confidence_levels} SET confidence_level=? WHERE evaluatorid=? AND id=?', $parms = array($data->$identifier / 100, $student_id, $properties->id));
                    }
                }

            }

            $urltogo = new moodle_url(($CFG->wwwroot . '/local/mpa/views/confidenceassignment.php'), null, null, null);
            redirect($urltogo);

        } else {
            $form->display();
        }

        echo $OUTPUT->footer();

    }

    public function render_login_required()
    {

        global $OUTPUT;

        echo $OUTPUT->header();

        echo get_string('loginrequired', 'local_mpa');

        echo $OUTPUT->footer();
    }
}