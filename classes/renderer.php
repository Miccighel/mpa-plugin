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

    public function render_student_summary(){

        global $OUTPUT;

        echo $OUTPUT->header();

        echo "Visualizzazione dati di tutti gli utenti";

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