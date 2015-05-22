<?php
/**
 * Definizione di nuove capacità per le varie tipologie di utenti.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

defined('MOODLE_INTERNAL') || die();

// In questo file vengono definite le varie tipologie di azioni eseguibili nel report con i relativi permessi.

$capabilities = array(
    'local/mpa:studentsummary' => array (
        'captype'      => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes'   => array(
            'student'        => CAP_ALLOW,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'          => CAP_ALLOW
        )
    ),
    'local/mpa:reportoverview' => array (
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array(
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'          => CAP_ALLOW
        )
    ),
);