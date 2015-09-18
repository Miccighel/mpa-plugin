<?php
/**
 * Varie classi di gestione per le caratteristiche del dominio da modellare nel sistema.
 *
 * @package    local
 * @subpackage mpa
 * @copyright  2015, Michael Soprano, miccighel@gmail.com
 */
defined('MOODLE_INTERNAL') || die();

class Student
{

    public $id;
    public $properties;
    public $exToEvalutateSolved = 0;
    public $exAssessed = 0;
    public $assignedGrades = 0;
    public $receivedGrades = 0;
    public $assignmentsSolved = 0;
    public $submissions = array();

    function Student($id)
    {
        $this->id = $id;
    }

    function getAssignedGrades()
    {
        return $this->assignedGrades;
    }

    function getReceivedGrades()
    {
        return $this->receivedGrades;
    }

    function getExAssessed()
    {
        return $this->exAssessed;
    }

    function getAssignmentsSolved()
    {
        return $this->assignmentsSolved;
    }

    function getExToEvaluateSolved()
    {
        return $this->exToEvalutateSolved;
    }

    function countExToEvaluateSolved()
    {
        global $DB;
        //$data = $DB->get_records_sql('SELECT * FROM {workshop_submissions} AS mws INNER JOIN {user} AS mu ON mws.authorid=mu.id');
        $this->exToEvalutateSolved = $DB->count_records_sql('SELECT COUNT(*) FROM {workshop_submissions} WHERE authorid=?', array($this->id));
    }

    function countExAssessed()
    {
        global $DB;
        //$data = $DB->get_records_sql('SELECT * FROM {workshop_assessments} AS mwa INNER JOIN {user} AS mu ON mwa.reviewerid=mu.id');
        $this->exAssessed = $DB->count_records_sql('SELECT COUNT(*) FROM {workshop_assessments} WHERE grade IS NOT NULL AND reviewerid = ?', array($this->id));
    }

    function countAssignedGrades()
    {
        global $DB;
        //$data = $DB->get_records_sql('SELECT * FROM ({workshop_assessments} AS mwa INNER JOIN {workshop_grades} AS mwg ON mwa.id = mwg.assessmentid) INNER JOIN {user} AS mu ON mwa.reviewerid=mu.id');
        $this->assignedGrades = $DB->count_records_sql('SELECT COUNT(*) FROM {workshop_assessments} AS mwa INNER JOIN {workshop_submissions} AS mws ON mwa.submissionid=mws.id WHERE mwa.grade IS NOT NULL AND reviewerid=?', array($this->id));
    }

    function countReceivedGrades()
    {
        global $DB;
        $this->receivedGrades = $DB->count_records_sql('SELECT COUNT(*) FROM {workshop_submissions} AS mws INNER JOIN {workshop_assessments} AS mwa ON mws.id=mwa.submissionid WHERE mwa.grade IS NOT NULL AND mws.authorid=?', array($this->id));
    }

    function countAssignmentsSolved()
    {
        global $DB;
        //$data = $DB->get_records_sql('SELECT * FROM {assign} AS ma INNER JOIN {assign_submission} AS mas ON ma.id=mas.assignment');
        $this->assignmentsSolved = $DB->count_records_sql('SELECT COUNT(*) FROM {assign_submission} WHERE userid = ?', array($this->id));
    }

    function setProperties($properties)
    {
        $this->properties = $properties;
    }

    function getProperties()
    {
        return $this->properties;
    }

    function addSubmission($submission)
    {
        array_push($this->submissions, $submission);
    }

    function getSubmissions()
    {
        return $this->submissions;
    }

    function loadStudentProperties()
    {

        global $DB;
        $this->setProperties($DB->get_records_sql('SELECT id,username,lastname,email FROM {user} WHERE id=?', array($this->id))[$this->id]);
    }

    function isTeacher($courseid)
    {
        global $DB;

        if ($courseid == null) {
            $flag = false;
        } else {

            $context = get_context_instance(CONTEXT_COURSE, $courseid);
            $roles = get_user_roles($context, $this->id);

            foreach ($roles as $role) {

                switch ($role->shortname) {
                    case 'teacher':
                        $flag = true;
                        break;
                    case 'editingteacher':
                        $flag = true;
                        break;
                    case 'manager':
                        $flag = true;
                        break;
                }

                if (is_siteadmin($this->id)) {
                    $flag = true;
                }

            }
        }

        return $flag;

    }

    function loadStudentActivity()
    {
        global $DB;

        $submissions = $DB->get_records_sql('SELECT w.name,w.intro,w.instructauthors,w.instructreviewers,w.conclusion,mws.id,w.id AS workshopid,authorid,mws.feedbackauthor,title,content FROM ({workshop_submissions} AS mws INNER JOIN {user} AS mu ON mws.authorid=mu.id) INNER JOIN {workshop} AS w ON mws.workshopid=w.id WHERE mws.authorid=?', array($this->id));

        foreach ($submissions as $object) {
            $submission = new Submission($object);
            $assessments = $DB->get_records_sql('SELECT id,submissionid,feedbackauthor,grade FROM {workshop_assessments} WHERE feedbackauthor IS NOT NULL AND submissionid=?', array($object->id));
            foreach ($assessments as $object) {
                $assessment = new Assessment($object);
                $grades = $DB->get_records_sql('SELECT wa.description,wg.id, wg.peercomment, wg.grade FROM {workshop_grades} AS wg INNER JOIN {workshopform_accumulative} AS wa ON wg.dimensionid=wa.id WHERE assessmentid=?', array($object->id));
                foreach ($grades as $object) {
                    $grade = new Grade($object);
                    $assessment->addGrade($grade);
                }
                $submission->addAssessment($assessment);
            }
            $this->addSubmission($submission);
        }
    }

    function loadGivenAssessments()
    {
        global $DB;

        $result = array();

        $givenAssessments = $DB->get_records_sql('SELECT mws.id,mwa.feedbackauthor,mwa.grade,mws.content,mw.instructauthors,mw.name,mcl.confidence_level FROM (({workshop_assessments}  AS mwa INNER JOIN {workshop_submissions} AS mws ON mwa.submissionid=mws.id) INNER JOIN {workshop} AS mw ON mws.workshopid = mw.id) INNER JOIN {mpa_confidence_levels} AS mcl ON mwa.reviewerid=mcl.evaluatorid AND mwa.submissionid = mcl.id WHERE mwa.reviewerid=? AND mwa.grade IS NOT NULL', array($this->id));
        foreach ($givenAssessments as $assessment) {
            array_push($result, new Assessment($assessment));
        }

        return $result;

    }

}

class Submission
{

    public $id;
    public $properties;
    public $assessments = array();

    function Submission($properties)
    {
        $this->id = $properties->id;
        $this->properties = $properties;
    }

    function getProperties()
    {
        return $this->properties;
    }

    function addAssessment($assessment)
    {
        array_push($this->assessments, $assessment);
    }

    function getAssessments()
    {
        return $this->assessments;
    }

    function loadAssessments()
    {
        global $DB;
        $assessments = $DB->get_records_sql('SELECT id,submissionid,reviewerid,feedbackauthor,grade FROM {workshop_assessments} WHERE feedbackauthor IS NOT NULL AND submissionid=?', array($this->id));
        foreach ($assessments as $assessmentProperties) {
            $assessment = new Assessment($assessmentProperties);
            $this->addAssessment($assessment);
        }
    }

}

class Assessment
{

    public $id;
    public $properties;
    public $grades = array();

    function Assessment($properties)
    {
        $this->id = $properties->id;
        $this->properties = $properties;
    }

    function getProperties()
    {
        return $this->properties;
    }

    function addGrade($grade)
    {
        array_push($this->grades, $grade);
    }

    function getGrades()
    {
        return $this->grades;
    }

    function loadGrades()
    {
        global $DB;
        $grades = $DB->get_records_sql('SELECT wa.description,wg.id, wg.peercomment, wg.grade FROM {workshop_grades} AS wg INNER JOIN {workshopform_accumulative} AS wa ON wg.dimensionid=wa.id WHERE assessmentid=?', array($this->id));
        foreach ($grades as $gradeProperties) {
            $grade = new Grade($gradeProperties);
            $this->addGrade($grade);
        }
    }

}

class Grade
{

    public $id;
    public $properties;

    function Grade($properties)
    {
        $this->id = $properties->id;
        $this->properties = $properties;
    }

    function getProperties()
    {
        return $this->properties;
    }

}