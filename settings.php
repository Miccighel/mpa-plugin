<?php
/**
 * Definisce le impostazioni del plugin.
 *
 * @package    report
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

defined('MOODLE_INTERNAL') || die;
$item = $ADMIN->add('reports', new admin_externalpage('mpa', get_string('pluginname', 'report_mpa'), "$CFG->wwwroot/report/mpa/index.php"));
