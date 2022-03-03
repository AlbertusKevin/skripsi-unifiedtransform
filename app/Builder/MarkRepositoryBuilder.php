<?php 
namespace App\Builder;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\FinalMark;
use App\Interfaces\MarkInterface;

class MarkRepositoryBuilder{
    private $model;
    private $construction;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function sessionIdFilter($session_id){
        $this->model = $this->model->where('session_id', $session_id);
        return $this;
    }

    public function semseterIdFilter($semester_id){
        $this->model = $this->model->where('semester_id', $semester_id);
        return $this;
    }

    public function classIdFilter($class_id){
        $this->model = $this->model->where('class_id', $class_id);
        return $this;
    }

    public function sectionIdFilter($section_id){
        $this->model = $this->model->where('section_id', $section_id);
        return $this;
    }

    public function courseIdFilter($course_id){
        $this->model = $this->model->where('course_id', $course_id);
        return $this;
    }

    public function build(){
        return $this->model->get();
    }
}