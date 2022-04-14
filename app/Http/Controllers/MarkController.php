<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Repositories\MarkRepository;
use App\Traits\AssignedTeacherCheck;
use App\Mediator\Mediator;
use App\Mediator\MediatorMark;

class MarkController extends Controller
{
    use SchoolSession, AssignedTeacherCheck;

    protected Mediator $mediator;

    public function __construct() {
        $this->mediator = new MediatorMark();
    }
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = $this->mediator->getData($this,"index",[
            "class_id" => $request->query('class_id', 0),
            "section_id" => $request->query('section_id', 0),
            "course_id" => $request->query('course_id', 0),
            "semester_id" => $request->query('semester_id', 0)
        ]);
        
        if(!$data["marks"]) {
            return abort(404);
        }

        if(!$data["grading_system_rules"]) {
            return abort(404);
        }

        foreach($data["marks"] as $mark_key => $mark) {
            foreach ($data["grading_system_rules"] as $key => $gradingSystemRule) {
                if($mark->final_marks >= $gradingSystemRule->start_at && $mark->final_marks <= $gradingSystemRule->end_at) {
                    $data["marks"][$mark_key]['point'] = $gradingSystemRule->point;
                    $data["marks"][$mark_key]['grade'] = $gradingSystemRule->grade;
                }
            }
        }

        return view('marks.results', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try{
            return view('marks.create', $this->mediator->getData($this,"create",[
                "request" => $request,
                "class_id" => $request->query('class_id'),
                "section_id" => $request->query('section_id'),
                "course_id" => $request->query('course_id'),
                "semester_id" => $request->query('semester_id', 0),
            ]));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showFinalMark(Request $request)
    {
        $class_id = $request->query('class_id');
        $section_id = $request->query('section_id');
        $course_id = $request->query('course_id');
        $semester_id = $request->query('semester_id', 0);

        $current_school_session_id = $this->getSchoolCurrentSession();
        $markRepository = new MarkRepository();
        $studentsWithMarks = $markRepository->getAll($current_school_session_id, $semester_id, $class_id, $section_id, $course_id);
        $studentsWithMarks = $studentsWithMarks->groupBy('student_id');

        $data = [
            'students_with_marks'       => $studentsWithMarks,
            'class_id'                  => $class_id,
            'class_name'                => $request->query('class_name'),
            'section_id'                => $section_id,
            'section_name'              => $request->query('section_name'),
            'course_id'                 => $course_id,
            'course_name'               => $request->query('course_name'),
            'semester_id'               => $semester_id,
            'current_school_session_id' => $current_school_session_id,
        ];

        return view('marks.submit-final-marks', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $this->checkIfLoggedInUserIsAssignedTeacher($request, $current_school_session_id);
        $rows = [];
        foreach($request->student_mark as $id => $stm) {
            foreach($stm as $exam => $mark){
                $row = [];
                $row['class_id'] = $request->class_id;
                $row['student_id'] = $id;
                $row['marks'] = $mark;
                $row['section_id'] = $request->section_id;
                $row['course_id'] = $request->course_id;
                $row['session_id'] = $request->session_id;
                $row['exam_id'] = $exam;

                $rows[] = $row;
            }
        }
        try {
            $markRepository = new MarkRepository();
            $markRepository->create($rows);

            return back()->with('status', 'Saving marks was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeFinalMark(Request $request) {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $this->checkIfLoggedInUserIsAssignedTeacher($request, $current_school_session_id);
        $rows = [];
        foreach($request->calculated_mark as $id => $cmark) {
                $row = [];
                $row['class_id'] = $request->class_id;
                $row['student_id'] = $id;
                $row['calculated_marks'] = $cmark;
                $row['final_marks'] = $request->final_mark[$id];
                $row['note'] = $request->note[$id];
                $row['section_id'] = $request->section_id;
                $row['course_id'] = $request->course_id;
                $row['session_id'] = $request->session_id;
                $row['semester_id'] = $request->semester_id;

                $rows[] = $row;
        }
        try {
            $markRepository = new MarkRepository();
            $markRepository->storeFinalMarks($rows);

            return back()->with('status', 'Submitting final marks was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function showCourseMark(Request $request)
    {
        $data = $this->mediator->getData($this,"show_course_mark",[
            "class_id" => $request->query('class_id'),
            "section_id" => $request->query('section_id'),
            "course_id" => $request->query('course_id'),
            "semester_id" => $request->query('semester_id'),
            "session_id" => $request->query('session_id'),
            "course_name" => $request->query('course_name'),
            "student_id" => $request->query('student_id')
        ]);

        if(!$data["final_marks"]) {
            return abort(404);
        }

        if(!$data["gradingSystemRules"]) {
            return abort(404);
        }

        dd($data["final_marks"], $data["gradingSystemRules"]);
        foreach($data["final_marks"] as $mark_key => $mark) {
            foreach ($data["gradingSystemRules"] as $key => $gradingSystemRule) {
                if($mark->final_marks >= $gradingSystemRule->start_at && $mark->final_marks <= $gradingSystemRule->end_at) {
                    dd($gradingSystemRule->point, $gradingSystemRule->grade);
                    $data["final_marks"][$mark_key]['point'] = $gradingSystemRule->point;
                    $data["final_marks"][$mark_key]['grade'] = $gradingSystemRule->grade;
                }
            }
        }

        return view('marks.student', $data);
    }
}
