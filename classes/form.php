<?php
/**
 * Classi per rappresentare diversi tipi di form.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */
require_once("$CFG->libdir/formslib.php");

class confidence_form extends moodleform
{
    public function definition()
    {
        global $CFG;

        $mform = $this->_form;

        $options = array();

        $options[get_string('notset', 'local_mpa')] = get_string('notset', 'local_mpa');

        for ($i = 1; $i <= 100; $i++) {
            $options['' . $i] = $i;
        }

        for ($i = 0; $i < $this->_customdata['items']; $i++) {
            $mform->addElement('select', 'level' . $i, get_string('chosenlevel', 'local_mpa') . " " . ($i) . "", $options, $attributes);
        }

        $this->add_action_buttons($cancel = false, $submitlabel = null);
    }
}

class configuration_form extends moodleform
{
    public function definition()
    {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('text', 'epsilon', get_string('epsilon', 'local_mpa'));
        $mform->addElement('text', 'gradingfactor', get_string('gradingfactor', 'local_mpa'));
        $mform->addElement('text', 'infinity', get_string('infinity', 'local_mpa'));
        $mform->addElement('text', 'teacherweight', get_string('teacherweight', 'local_mpa'));

        $this->add_action_buttons($cancel = false, $submitlabel = null);
    }
}