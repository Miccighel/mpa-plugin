<?php
/**
 * Report settings
 *
 * @package    report
 * @subpackage mpa
 * @copyright  2015 Michael Soprano* @license
 */

defined('MOODLE_INTERNAL') || die;
$ADMIN->add('reports', new admin_externalpage('mpa', get_string('mpa', 'report_mpa'), "$CFG->wwwroot/report/mpa/index.php"));
// no report settings
$settings = null;