<?php 
namespace App\Builder;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\FinalMark;
use App\Interfaces\MarkInterface;

interface MarkRepositoryBuilder{
    public function sessionIdFilter($session_id);
    public function semseterIdFilter($semester_id);
    public function classIdFilter($class_id);
    public function sectionIdFilter($section_id);
    public function courseIdFilter($course_id);
    public function build();
}