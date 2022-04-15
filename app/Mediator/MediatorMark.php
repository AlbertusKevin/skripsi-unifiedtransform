<?php 
namespace App\Mediator;

class MediatorMark extends Mediator{
    public function getData($sender, $event, $data = []){
        if($event == "index"){
            $gradingSystem = $this->gradeSystemRepository->getGradingSystem($this->school_session_id, $data["semester_id"], $data["class_id"]);

            return [
                'current_school_session_id' => $this->school_session_id,
                'semesters'                 => $this->semesterRepository->getAll($this->school_session_id),
                'classes'                   => $this->schoolClassRepository->getAllBySession($this->school_session_id),
                'marks'                     => $this->markRepository->getAllFinalMarks($this->school_session_id, $data["semester_id"], $data["class_id"], $data["section_id"], $data["course_id"]),
                'grading_system_rules'      => (!$gradingSystem) ? null : $this->gradeRulesRepository->getAll($this->school_session_id, $gradingSystem->id),
            ];
        }

        if($event == "create"){
            $this->checkIfLoggedInUserIsAssignedTeacher($data["request"], $this->school_session_id);
            $final_marks_submit_count = $this->markRepository->getFinalMarksCount($this->school_session_id, $data["semester_id"], $data["class_id"], $data["section_id"], $data["course_id"]);
            
            return [
                'academic_setting'          => $this->academic_setting,
                'exams'                     => $this->examRepository->getAll($this->school_session_id, $data["semester_id"], $data["class_id"]),
                'students_with_marks'       => $this->markRepository
                        ->getAll($this->school_session_id, $data["semester_id"], $data["class_id"], $data["section_id"], $data["course_id"])
                        ->groupBy('student_id'),
                'class_id'                  => $data["class_id"],
                'section_id'                => $data["section_id"],
                'course_id'                 => $data["course_id"],
                'semester_id'               => $data["semester_id"],
                'final_marks_submitted'     => ($final_marks_submit_count > 0) ? true : false,
                'sectionStudents'           => $this->userRepository->getAllStudents($this->school_session_id, $data["class_id"], $data["section_id"]),
                'current_school_session_id' => $this->school_session_id,
            ];
        }

        if($event == "show_course_mark"){
            $gradingSystem = $this->gradeSystemRepository->getGradingSystem($data["session_id"], $data["semester_id"], $data["class_id"]);

            return [
                "marks" => $this->markRepository->getAllByStudentId($data["session_id"], $data["semester_id"], $data["class_id"], $data["section_id"], $data["course_id"], $data["student_id"]),
                "final_marks" => $this->markRepository->getAllFinalMarksByStudentId($data["session_id"], $data["student_id"], $data["semester_id"], $data["class_id"], $data["section_id"], $data["course_id"]),
                "grading_system_rules" => (!$gradingSystem) ? null : $this->gradeRulesRepository->getAll($data["session_id"], $gradingSystem->id),
                'course_name' => $data["course_name"],
            ];
        }
    }
}