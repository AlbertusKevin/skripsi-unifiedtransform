<?php 
namespace App\Mediator;

use App\Traits\StrategyContext;

class MediatorMark extends Mediator{
    use StrategyContext;
    protected array $type;

    public function __construct() {
        parent::__construct();
        $this->type = [
            "MARK" => "MARK",
            "FINAL_MARK" => "FINAL_MARK"
        ];
    }

    public function getData($sender, $event, $data = []){
        if($event == "index"){
            $gradingSystem = $this->gradeSystemRepository->getGradingSystem($this->school_session_id, $data["semester_id"], $data["class_id"]);

            $filter = array_merge(["session_id" => $this->school_session_id], $data);
            $pivotData = ["student"];
            $marks = $this->markRepository->getMarks($filter, $this->type["FINAL_MARK"], $pivotData);

            return [
                'current_school_session_id' => $this->school_session_id,
                'semesters'                 => $this->semesterRepository->getAll($this->school_session_id),
                'classes'                   => $this->schoolClassRepository->getAllBySession($this->school_session_id),
                'marks'                     => $marks,
                'grading_system_rules'      => (!$gradingSystem) ? null : $this->gradeRulesRepository->getAll($this->school_session_id, $gradingSystem->id),
            ];
        }

        if($event == "create"){
            $this->checkIfLoggedInUserIsAssignedTeacher($data["request"], $this->school_session_id);
            $final_marks_submit_count = $this->markRepository->getFinalMarksCount($this->school_session_id, $data["semester_id"], $data["class_id"], $data["section_id"], $data["course_id"]);
            
            $filter = array_merge([
                "exam_id" => true,
                "session_id" => $this->school_session_id
            ], $data);

            $pivotData = ['student','exam'];
            $studentsWithMarks = $this->markRepository->getMarks($filter, $this->type["MARK"], $pivotData)->groupBy('student_id');

            $this->setStrategyContext(STUDENT);
            $data["session_id"] =  $this->school_session_id;
            $sectionStudents = $this->context->executeGetAll($data);

            return [
                'academic_setting'          => $this->academic_setting,
                'exams'                     => $this->examRepository->getAll($this->school_session_id, $data["semester_id"], $data["class_id"]),
                'students_with_marks'       => $studentsWithMarks,
                'class_id'                  => $data["class_id"],
                'section_id'                => $data["section_id"],
                'course_id'                 => $data["course_id"],
                'semester_id'               => $data["semester_id"],
                'final_marks_submitted'     => ($final_marks_submit_count > 0) ? true : false,
                'sectionStudents'           => $sectionStudents,
                'current_school_session_id' => $this->school_session_id,
            ];
        }

        if($event == "show_course_mark"){
            $gradingSystem = $this->gradeSystemRepository->getGradingSystem($data["session_id"], $data["semester_id"], $data["class_id"]);
            $filter = array_merge($data, [
                "exam_id" => true,
            ]);
            $pivotData = ['student','exam'];
            $marks = $this->markRepository->getMarks($filter, $this->type["MARK"], $pivotData);
    
            unset($filter["exam_id"]);
            $pivotData = ['student'];
            $finalMarks = $$this->markRepository->getMarks($filter, $this->type["FINAL_MARK"], $pivotData);

            return [
                "marks" => $marks,
                "final_marks" => $finalMarks,
                "grading_system_rules" => (!$gradingSystem) ? null : $this->gradeRulesRepository->getAll($data["session_id"], $gradingSystem->id),
                'course_name' => $data["course_name"],
            ];
        }
    }
}