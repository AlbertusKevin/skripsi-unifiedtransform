<?php 
namespace App\Singleton;

use App\Strategy\ConcreteStrategy\TeacherStrategy;

class SingletonTeacherStrategyRepository{
    private static $concreteStrategy = null;

    public static function getInstance(){
        if(self::$concreteStrategy == null){
            self::$concreteStrategy = new TeacherStrategy();
        }

        return self::$concreteStrategy;
    }
}
?>