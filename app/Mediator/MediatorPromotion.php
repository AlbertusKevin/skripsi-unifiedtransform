<?php 
namespace App\Mediator;

use App\Traits\StrategyContext;

class MediatorPromotion extends Mediator{
    use StrategyContext;
    public function getData($sender, $event, $data = []){        
        if($event == "index"){
            $previousSession = $this->schoolSessionRepository->getPreviousSession();

            if(count($previousSession) < 1) {
                return ["error" => 'No previous session'];
            }

            return [
                'previousSessionClasses'        => $this->promotionRepository->getClasses($previousSession['id']),
                'class_id'                      => $data["class_id"],
                'previousSessionSections'       => $this->promotionRepository->getSections($previousSession['id'], $data["class_id"]),
                'currentSessionSectionsCounts'  => $this->promotionRepository->getSectionsBySession($this->school_session_id)->count(),
                'previousSessionId'             => $previousSession['id'],
            ];
        }

        if($event == "create"){
            $latest_school_session = $this->schoolSessionRepository->getLatestSession();
            $this->setStrategyContext(STUDENT);
            $students = $this->context->executeGetAll($data);

            return [
                'students'      => $students,
                'schoolClass'   => $this->schoolClassRepository->findById($data["class_id"]),
                'section'       => $this->schoolSectionRepository->findById($data["section_id"]),
                'school_classes'=> $this->schoolClassRepository->getAllBySession($latest_school_session->id),
            ];
        }
    }
}