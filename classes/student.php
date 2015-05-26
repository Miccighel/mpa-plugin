<?php
/**
 * Varie classi di gestione per le caratteristiche del dominio da modellare nel sistema.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */

defined('MOODLE_INTERNAL') || die();

class Student {

    public $properties;
    public $submissions = array();

    function Student($properties){
        $this->properties=$properties;
    }

    function addSubmission($submission){
        array_push($this->submissions,$submission);
    }

}

class Submission {

    public $properties;
    public $assessments=array();

    function Submission($properties){
        $this->properties=$properties;
    }

    function getProperties(){
        return $this->properties;
    }

    function addAssessment($assessment){
        array_push($this->assessments,$assessment);
    }

}

class Assessment {

    public $properties;
    public $grades=array();

    function Assessment($properties){
        $this->properties=$properties;
    }

    function getProperties(){
        return $this->properties;
    }

    function addGrade($grade){
        array_push($this->grades,$grade);
    }

}

class Grade {

    public $properties;

    function Grade($properties){
        $this->properties=$properties;
    }

    function getProperties(){
        return $this->properties;
    }

}