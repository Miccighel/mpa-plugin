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

class local_mpa_renderer extends plugin_renderer_base
{

    public function render_student_summary($students_data)
    {

        global $OUTPUT;

        echo $OUTPUT->header();

        $table = new html_table();
        $table->head = array(get_string('username', 'local_mpa'), get_string('ex_to_evaluate_solved', 'local_mpa'), get_string('ex_assessed', 'local_mpa'), get_string('grades', 'local_mpa'), get_string('assignments_solved', 'local_mpa'));

        foreach ($students_data as $student) {
            $info = $student[4];
            $table->data[] = array($student[4]->username, $student[0], $student[1], $student[2], $student[3]);
        }

        echo html_writer::table($table);

        echo $OUTPUT->footer();
    }

    public function render_student_feedback($studentHandler)
    {

        global $OUTPUT;

        echo $OUTPUT->header();

        $submissions = $studentHandler->getSubmissions();

        foreach ($submissions as $submission) {

            $subProperties = $submission->getProperties();
            $assessments = $submission->getAssessments();

            echo html_writer::tag('h2', get_string('submission', 'local_mpa'));

            echo html_writer::start_tag('table', array('class' => 'generaltable'));
            echo html_writer::start_tag('thead');
            echo html_writer::start_tag('tr');
            echo html_writer::tag('th', get_string('subid', 'local_mpa'), array('class' => 'header'));
            echo html_writer::tag('th', get_string('workname', 'local_mpa'), array('class' => 'header'));
            echo html_writer::tag('th', get_string('workintroduction', 'local_mpa'), array('class' => 'header'));
            echo html_writer::tag('th', get_string('instructauthors', 'local_mpa'), array('class' => 'header'));
            echo html_writer::tag('th', get_string('instructreviewers', 'local_mpa'), array('class' => 'header'));
            echo html_writer::tag('th', get_string('title', 'local_mpa'), array('class' => 'header'));
            echo html_writer::tag('th', get_string('content', 'local_mpa'), array('class' => 'header'));
            echo html_writer::tag('th', get_string('subfeedbackauthor', 'local_mpa'), array('class' => 'header'));
            echo html_writer::end_tag('tr');
            echo html_writer::end_tag('thead');
            echo html_writer::start_tag('tr');
            echo html_writer::tag('td', $subProperties->id);
            echo html_writer::tag('td', $subProperties->name);
            echo html_writer::tag('td', $subProperties->intro);
            echo html_writer::tag('td', $subProperties->instructauthors);
            echo html_writer::tag('td', $subProperties->instructreviewers);
            echo html_writer::tag('td', $subProperties->title);
            echo html_writer::tag('td', $subProperties->content);
            echo html_writer::tag('td', $subProperties->feedbackauthor);
            echo html_writer::end_tag('tr');
            echo html_writer::end_tag('table');

            foreach ($assessments as $assessment) {

                echo html_writer::tag('h3', get_string('assessment', 'local_mpa'));

                $assProperties = $assessment->getProperties();
                $grades = $assessment->getGrades();

                echo html_writer::start_tag('table', array('class' => 'generaltable'));
                echo html_writer::start_tag('thead');
                echo html_writer::start_tag('tr');
                echo html_writer::tag('th', get_string('assid', 'local_mpa'), array('class' => 'header'));
                echo html_writer::tag('th', get_string('assgrade', 'local_mpa'), array('class' => 'header'));
                echo html_writer::tag('th', get_string('assfeedbackauthor', 'local_mpa'), array('class' => 'header'));
                echo html_writer::tag('th', '', array('class' => 'header'));
                echo html_writer::end_tag('tr');
                echo html_writer::end_tag('thead');
                echo html_writer::start_tag('tr');
                echo html_writer::tag('td', $subProperties->id . '.' . $assProperties->id);
                echo html_writer::tag('td', $assProperties->grade);
                echo html_writer::tag('td', $assProperties->feedbackauthor);
                echo html_writer::end_tag('tr');
                echo html_writer::end_tag('table');

                echo html_writer::tag('h4', get_string('grade', 'local_mpa'));

                echo html_writer::start_tag('table', array('class' => 'generaltable'));
                echo html_writer::start_tag('tr');
                echo html_writer::tag('th', get_string('gradid', 'local_mpa'), array('class' => 'header'));
                echo html_writer::tag('th', get_string('gradedescription', 'local_mpa'), array('class' => 'header'));
                echo html_writer::tag('th', get_string('grade', 'local_mpa'), array('class' => 'header'));
                echo html_writer::tag('th', get_string('peercomment', 'local_mpa'), array('class' => 'header'));
                echo html_writer::end_tag('tr');

                foreach ($grades as $grade) {

                    $gradProperties = $grade->getProperties();

                    echo html_writer::start_tag('tr');
                    echo html_writer::tag('td', $subProperties->id . '.' . $assProperties->id . '.' . $gradProperties->id);
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

    public function render_capability_error()
    {

        global $OUTPUT;

        echo $OUTPUT->header();

        echo get_string('capabilityerror', 'local_mpa');

        echo $OUTPUT->footer();
    }

    public function render_local_overview()
    {
        global $OUTPUT;

        echo $OUTPUT->header();

        echo "Pagina di learning";

        echo $OUTPUT->footer();
    }
}