<?php 
namespace App\Mediator;

use App\Traits\StrategyContext;

class MediatorAcademicSetting extends Mediator{
    use StrategyContext;

    public function getData($sender, $event, $data = []){
        if($event == "index"){
            $this->setStrategyContext(TEACHER);

            return [
                'current_school_session_id' => $this->school_session_id,
                'school_sessions'           => $this->schoolSessionRepository->getAll(),
                'school_classes'            => $this->schoolClassRepository->getAllBySession($this->school_session_id),
                'school_sections'           => $this->schoolSectionRepository->getAllBySession($this->school_session_id),
                'teachers'                  => $this->context->executeGetAll(),
                'courses'                   => $this->courseRepository->getAll($this->school_session_id),
                'semesters'                 => $this->semesterRepository->getAll($this->school_session_id),
                "latest_school_session_id"  => $this->schoolSessionRepository->getLatestSession()->id,
                "academic_setting"          => $this->academic_setting
            ];
        }
    }
}