<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\FinalMark;
use App\Interfaces\MarkInterface;
use App\Builder;
use App\Builder\MarkRepositoryBuilder;

class MarkRepository implements MarkInterface {
    public function create($rows) {
        try {
            foreach($rows as $row){
                Mark::updateOrCreate([
                    'exam_id' => $row['exam_id'],
                    'student_id' => $row['student_id'],
                    'session_id' => $row['session_id'],
                    'class_id' => $row['class_id'],
                    'section_id' => $row['section_id'],
                    'course_id' => $row['course_id']
                ],['marks' => $row['marks']]);
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to update students marks. '.$e->getMessage());
        }
    }

    public function getMarks(array $data, string $type, array $with = []){
        // set model yang akan digunakan untuk berinteraksi dengan db
        $model = ($type == "MARK") ? new Mark() : new FinalMark();

        // buat objek buildernya
        $builder = new MarkRepositoryBuilder($model);
        // tambahkan jika butuh pivot data
        if(count($with) != 0) $builder = $builder->withPivotData($with);
        
        //query berdasarkan filter yang ada
        if(array_key_exists("exam_id",$data)){ 
            $exam_ids = Exam::where('semester_id', $data["semester_id"])->pluck('id')->toArray();
            $builder = $builder->examIdsFilter($exam_ids);
        }
        
        // dd(array_key_exists("semester_id",$data) && $model == Mark::class);
        if(array_key_exists("session_id",$data)) $builder = $builder->sessionIdFilter($data["session_id"]);
        if(array_key_exists("semester_id",$data) && $model != new Mark()) $builder = $builder->semesterIdFilter($data["semester_id"]);
        if(array_key_exists("class_id",$data)) $builder = $builder->classIdFilter($data["class_id"]);
        if(array_key_exists("section_id",$data)) $builder = $builder->sectionIdFilter($data["section_id"]);
        if(array_key_exists("course_id",$data)) $builder = $builder->courseIdFilter($data["course_id"]);
        if(array_key_exists("student_id",$data)) $builder = $builder->studentIdFilter($data["student_id"]);

        // ambil objeknya
        return $builder->buildGet();
    }

    public function getFinalMarksCount($session_id, $semester_id, $class_id, $section_id, $course_id) {
        return FinalMark::where('session_id', $session_id)
                    ->where('semester_id', $semester_id)
                    ->where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->where('course_id', $course_id)
                    ->count();
    }

    public function storeFinalMarks($rows) {
        try {
            foreach($rows as $row){
                FinalMark::updateOrCreate([
                    'semester_id' => $row['semester_id'],
                    'student_id' => $row['student_id'],
                    'session_id' => $row['session_id'],
                    'class_id' => $row['class_id'],
                    'section_id' => $row['section_id'],
                    'course_id' => $row['course_id']
                ],[
                    'calculated_marks' => $row['calculated_marks'],
                    'final_marks' => $row['final_marks'],
                    'note'  => $row['note'],
                ]);
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to update students final marks. '.$e->getMessage());
        }
    }
}