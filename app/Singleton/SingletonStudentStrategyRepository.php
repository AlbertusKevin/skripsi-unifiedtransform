<?php 
namespace App\Singleton;

use App\Strategy\ConcreteStrategy\StudentStrategy;

class SingletonStudentStrategyRepository{
    private static $concreteStrategy = null;

    public static function getInstance(){
        if(self::$concreteStrategy == null){
            self::$concreteStrategy = new StudentStrategy();
        }

        return self::$concreteStrategy;
    }
}
?>