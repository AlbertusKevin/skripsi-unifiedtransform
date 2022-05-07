<?php 
namespace App\Mediator;

use App\Traits\StrategyContext;

class MediatorAttendance extends Mediator{
    use StrategyContext;

    public function getData($sender, $event, $data = []){
        if($event == "create"){
            $this->setStrategyContext(STUDENT);
            $param = array_merge($data, ["session_id" => $this->school_session_id]);
            $student = $this->context->executeGetAll($param);
            return [
                'current_school_session_id' => $this->school_session_id,
                'academic_setting'  => $this->academic_setting,
                'student_list'      => $student,
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
            $this->setStrategyContext(STUDENT);

            return [
                'attendances'   => $this->attendanceRepository->getStudentAttendance($this->school_session_id, $data["id"]),
                'student'       => $this->context->executeFind($data["id"])
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