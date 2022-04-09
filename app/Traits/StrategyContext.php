<?php 
namespace App\Traits;

use App\Strategy\ConcreteStrategy\StudentStrategy;
use App\Strategy\ConcreteStrategy\TeacherStrategy;

trait StrategyContext{
    private function setStrategyContext($user){
        switch ($user) {
            case TEACHER:
                $this->context->setStrategy(new TeacherStrategy());
                break;
            default:
                $this->context->setStrategy(new StudentStrategy());
                break;
        }
    }
}

?>