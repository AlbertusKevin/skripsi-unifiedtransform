<?php 
namespace App\Mediator;

class MediatorPromotion extends Mediator{
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

            return [
                'students'      => $this->userRepository->getAllStudents($data["session_id"], $data["class_id"], $data["section_id"]),
                'schoolClass'   => $this->schoolClassRepository->findById($data["class_id"]),
                'section'       => $this->schoolSectionRepository->findById($data["section_id"]),
                'school_classes'=> $this->schoolClassRepository->getAllBySession($latest_school_session->id),
            ];
        }
    }
}