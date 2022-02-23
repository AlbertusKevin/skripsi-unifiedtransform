<?php 
namespace App\Facade;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class TimeManipulationFacade{
    public static function getTimeNow(){
        return Carbon::now();
    }

    public static function getToday(){
        return Carbon::today();
    }

    public static function convertDateToString(CarbonInterface $date){
        return $date->toDateString();
    }
}