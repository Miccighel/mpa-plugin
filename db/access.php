<?php
/**
 * Definizione di nuove capacità per le varie tipologie di utenti.
 *
 * @package    report
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'report/mpa:viewdata' => array (
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
    ),
);