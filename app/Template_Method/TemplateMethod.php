<?php

namespace App\Template_Method;

use App\Http\Controllers\Controller;
use App\Mediator\Mediator;
use Illuminate\Http\Request;

abstract class TemplateMethod extends Controller
{
    protected function prepareData(
        Request $request, 
        array $keys_param, 
        array $keys_null,
        string $key_mark, 
        Mediator $mediator = null, 
        Controller $sender,
        string $event
    ){
        $param = $this->getQueryParameter($request, $keys_param);
        $data = $mediator->getData($sender,$event, $param);
        $this->isNullData($data, $keys_null);
        $data = $this->getAllMarks($data, $key_mark);

        return $data;
    }

    protected function getQueryParameter(Request $request, array $keys){
        $data = [];

        foreach($keys as $key => $default_value){
            $data[$key] = $request->query($key, $default_value);
        }

        return $data;
    }

    protected function isNullData($data, array $keys){
        foreach($keys as $key){
            if(!$data[$key]) {
                return abort(404);
            }
        }
    }

    public function getAllMarks($data, $key_mark){
        foreach($data[$key_mark] as $mark_key => $mark) {
            foreach ($data["grading_system_rules"] as $key => $gradingSystemRule) {
                if($mark->final_marks >= $gradingSystemRule->start_at && $mark->final_marks <= $gradingSystemRule->end_at) {
                    $data[$key_mark][$mark_key]['point'] = $gradingSystemRule->point;
                    $data[$key_mark][$mark_key]['grade'] = $gradingSystemRule->grade;
                }
            }
        }

        return $data;
    }
}