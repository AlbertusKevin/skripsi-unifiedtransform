<?php 
namespace App\Traits;

use App\Singleton\Singleton;
use App\Singleton\SingletonStudentStrategyRepository;
use App\Singleton\SingletonTeacherStrategyRepository;
use App\Strategy\ConcreteStrategy\StudentStrategy;
use App\Strategy\ConcreteStrategy\TeacherStrategy;

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
        
        // switch ($user) {
        //     case TEACHER:
        //         $this->context->setStrategy(new TeacherStrategy());
        //         break;
        //     default:
        //         $this->context->setStrategy(new StudentStrategy());
        //         break;
        // }
    }
}

?>