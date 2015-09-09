<?php
/**
 * Business logic del plugin.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

function print_page_attributes($title, $heading, $context, $layout)
{
    global $PAGE;
    $PAGE->set_title(get_string($title, 'local_mpa'));
    $PAGE->set_heading(get_string($heading, 'local_mpa'), 3);
    $PAGE->set_context($context);
    $PAGE->set_pagelayout($layout);
}

// In poche parole, ritorna gli studenti che hanno fatto qualcosa.

function get_active_students()
{

    global $DB;

    $all_students = array();
    $final_students = array();

    $students = $DB->get_records_sql('SELECT id FROM {user}');

    foreach ($students as $student) {
        $object = new Student($student->id);
        $object->loadStudentProperties();
        $object->loadStudentActivity();
        $object->countAssignmentsSolved();
        $object->countExAssessed();
        $object->countExToEvaluateSolved();
        $object->countReceivedGrades();
        $object->countAssignedGrades();
        array_push($all_students, $object);
    }

    foreach ($all_students as $student) {
        if ($student->getExAssessed() != 0 || $student->getExToEvaluateSolved() != 0 || $student->getAssignmentsSolved() != 0) {
            array_push($final_students, $student);
        }
    }

    return $final_students;
}

function array2csv(array &$array)
{
    if (count($array) == 0) {
        return null;
    }
    ob_start();
    $df = fopen("php://output", 'w');
    fputcsv($df, array_keys(reset($array)));
    foreach ($array as $row) {
        fputcsv($df, $row);
    }
    fclose($df);
    return ob_get_clean();
}

function download_send_headers($filename)
{

    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");

}