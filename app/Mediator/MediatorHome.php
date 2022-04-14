<?php 
namespace App\Mediator;

class MediatorHome extends Mediator{
    public function getData($sender, $event, $data = []){
        if($event == "index"){
            return [
                'classCount'    => $this->schoolClassRepository->getAllBySession($this->school_session_id)->count(),
                'studentCount'  => $this->userRepository->getAllStudentsBySessionCount($this->school_session_id),
                'teacherCount'  => $this->userRepository->getAllTeachers()->count(),
                'notices'       => $this->noticeRepository->getAll($this->school_session_id),
                'maleStudentsBySession' => $this->promotionRepository->getMaleStudentsBySessionCount($this->school_session_id),
            ];
        }
    }
}