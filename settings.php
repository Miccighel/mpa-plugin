<?php
/**
 * Impostazioni del plugin.
 *
 * @package    report
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

defined('MOODLE_INTERNAL') || die;
$ADMIN->add('reports', new admin_externalpage('mpa', get_string('pluginname', 'report_mpa'), "$CFG->wwwroot/report/mpa/index.php"));
// no report settings
$settings = null;