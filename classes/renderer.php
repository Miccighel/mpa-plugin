<?php
/**
 * Classe contenente le viste del plugin
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/mpa/locallib.php');

class local_mpa_renderer extends plugin_renderer_base {

    public function render_student_summary($students_data){

        global $OUTPUT;

        echo $OUTPUT->header();

        $table = new html_table();
        $table->head=array(get_string('username','local_mpa'),get_string('ex_to_evaluate_solved','local_mpa'),get_string('ex_assessed','local_mpa'),get_string('grades','local_mpa'),get_string('assignments_solved','local_mpa'));

        foreach($students_data as $student){
            $info = $student[4];
            $table->data[]=array($student[4]->username,$student[0],$student[1],$student[2],$student[3]);
        }

        echo html_writer::table($table);

        echo $OUTPUT->footer();
    }

    public function render_student_feedback($studentHandler){

        global $OUTPUT;

        echo $OUTPUT->header();

        echo '<pre>';
        print_r($studentHandler);
        echo '</pre>';

        echo $OUTPUT->footer();
    }

    public function render_capability_error(){

        global $OUTPUT;

        echo $OUTPUT->header();

        echo get_string('capabilityerror','local_mpa');

        echo $OUTPUT->footer();
    }

    public function render_local_overview(){
        global $OUTPUT;

        echo $OUTPUT->header();

        echo "Pagina di learning";

        echo $OUTPUT->footer();
    }
}