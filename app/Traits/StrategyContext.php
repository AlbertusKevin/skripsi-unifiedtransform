<?php 
namespace App\Traits;

use App\Singleton\SingletonStudentStrategyRepository;
use App\Singleton\SingletonTeacherStrategyRepository;

trait StrategyContext{
    private function setStrategyContext($user){
        switch ($user) {
            case TEACHER:
                $this->context->setStrategy(SingletonTeacherStrategyRepository::getInstance());
                break;
            default:
                $this->context->setStrategy(SingletonStudentStrategyRepository::getInstance());
                break;
        }
    }
}

?>