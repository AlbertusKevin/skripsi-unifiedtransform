<?php 
namespace App\Mediator;

use App\Traits\StrategyContext;

class MediatorHome extends Mediator{
    use StrategyContext;

    public function getData($sender, $event, $data = []){
        if($event == "index"){
            $param = [
                "session_id" => $this->school_session_id,
                "by_session_count" => true
            ];

            $this->setStrategyContext(STUDENT);
            $studentCount = $this->context->executeGetAll($param);

            $this->setStrategyContext(TEACHER);
            $teacherCount = $this->context->executeGetAll()->count();
    
            return [
                'classCount'    => $this->schoolClassRepository->getAllBySession($this->school_session_id)->count(),
                'studentCount'  => $studentCount,
                'teacherCount'  => $teacherCount,
                'notices'       => $this->noticeRepository->getAll($this->school_session_id),
                'maleStudentsBySession' => $this->promotionRepository->getMaleStudentsBySessionCount($this->school_session_id),
            ];
        }
    }
}