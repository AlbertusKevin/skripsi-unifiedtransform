<?php 
namespace App\Mediator;

class MediatorAttendance extends Mediator{
    public function getData($sender, $event, $data = []){
        if($event == "create"){
            return [
                'current_school_session_id' => $this->school_session_id,
                'academic_setting'  => $this->academic_setting,
                'student_list'      => $this->userRepository->getAllStudents(
                        $this->school_session_id, 
                        $data["class_id"], 
                        $data["section_id"]
                    ),
                'school_class'      => $this->schoolClassRepository->findById($data["class_id"]),
                'school_section'    => $this->schoolSectionRepository->findById($data["section_id"]),
                'attendance_count'  => $this->getAttendances($this->academic_setting->attendance_type,$data)->count(),
            ];
        }
        
        if($event == "show"){
            $data = array_merge(["current_school_session_id" => $this->school_session_id],$data);
            return ["attendances" => $this->getAttendances($this->academic_setting->attendance_type, $data)];
        }

        if($event == "show_student_attendace"){
            return [
                'attendances'   => $this->attendanceRepository->getStudentAttendance($this->school_session_id, $data["id"]),
                'student'       => $this->userRepository->findStudent($data["id"]),
            ];
        }
    }

    private function getAttendances(string $attendance_type, array $data){
        if($attendance_type == 'section') {
            return $this->attendanceRepository->getSectionAttendance($data["class_id"], $data["section_id"], $this->school_session_id);
        }

        return $this->attendanceRepository->getCourseAttendance($data["class_id"], $data["course_id"], $this->school_session_id);
    }
}