<?php 
namespace App\Mediator;

class MediatorExam extends Mediator{
    public function getData($sender, $event, $data = []){
        if($event == "index"){
            return [
                'current_school_session_id' => $this->school_session_id,
                'semesters'                 => $this->semesterRepository->getAll($this->school_session_id),
                'classes'                   => $this->schoolClassRepository->getAllBySession($this->school_session_id),
                'exams'                     => $this->examRepository->getAll(
                        $this->school_session_id, $data["semester_id"], $data["class_id"]
                    ),
                'teacher_courses'           => $this->assignedTeacherRepository->getTeacherCourses(
                        $this->school_session_id, $data["teacher_id"], $data["semester_id"]
                    )
            ];
        }

        if($event == "create"){
            if(auth()->user()->role == "teacher") {
                $school_classes = $this->getTeacherSchoolClasses($this->school_session_id);
            } else {
                $school_classes = $this->schoolClassRepository->getAllBySession($this->school_session_id);
            }

            return [
                'current_school_session_id' => $this->school_session_id,
                'semesters'                 => $this->semesterRepository->getAll($this->school_session_id),
                'classes'                   => $school_classes,
            ];
        }   
    }

    private function getTeacherSchoolClasses($current_school_session_id){
        $teacher_id = auth()->user()->id;
        $assigned_classes = $this->schoolClassRepository->getAllBySessionAndTeacher($current_school_session_id, $teacher_id);

        $school_classes = [];
        $i = 0;

        foreach($assigned_classes as $assigned_class) {
            $school_classes[$i] = $assigned_class->schoolClass;
            $i++;
        }

        return $school_classes;
    }
}