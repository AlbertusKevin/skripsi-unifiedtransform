<?php 
namespace App\Builder;

class MarkRepositoryBuilder{
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function withPivotData(array $pivot_data){
        $this->model = $this->model->with($pivot_data);
        return $this;
    }

    public function sessionIdFilter($session_id){
        $this->model = $this->model->where('session_id', $session_id);
        return $this;
    }

    public function semesterIdFilter($semester_id){
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

    public function studentIdFilter($student_id){
        $this->model = $this->model->where('student_id', $student_id);
        return $this;
    }

    public function examIdsFilter($exam_ids){
        $this->model = $this->model->whereIn('exam_id', $exam_ids);
        return $this;
    }

    public function buildGet(){
        return $this->model->get();
    }
}