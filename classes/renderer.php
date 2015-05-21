<?php
/**
 * Classe contenente le viste del plugin
 *
 * @package    report
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/report/mpa/locallib.php');

class report_mpa_renderer extends plugin_renderer_base {

    public function render_student_summary($students_data){

        global $OUTPUT;

        echo $OUTPUT->header();

        $table = new html_table();
        $table->head=array(get_string('username','report_mpa'),get_string('ex_to_evaluate_solved','report_mpa'),get_string('ex_assessed','report_mpa'),get_string('grades','report_mpa'),get_string('assignments_solved','report_mpa'));

        foreach($students_data as $student){
            $info = $student[4];
            $table->data[]=array($student[4]->username,$student[0],$student[1],$student[2],$student[3]);
        }

        echo html_writer::table($table);

        echo $OUTPUT->footer();
    }

    public function render_capability_error(){
        global $OUTPUT;

        echo $OUTPUT->header();

        echo get_string('capabilityerror','report_mpa');

        echo $OUTPUT->footer();
    }

    public function render_report_overview(){
        global $OUTPUT;

        echo $OUTPUT->header();

        echo "Pagina di learning";

        echo $OUTPUT->footer();
    }
}