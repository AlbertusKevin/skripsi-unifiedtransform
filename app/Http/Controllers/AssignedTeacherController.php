<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\SemesterInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Http\Requests\TeacherAssignRequest;
use App\Repositories\AssignedTeacherRepository;
use App\Template_Method\TemplateMethod;

class AssignedTeacherController extends TemplateMethod
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $semesterRepository;

    /**
    * Create a new Controller instance
    * 
    * @param SchoolSessionInterface $schoolSessionRepository
    * @return void
    */
    public function __construct(SchoolSessionInterface $schoolSessionRepository,
    SemesterInterface $semesterRepository) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->semesterRepository = $semesterRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function getTeacherCourses(Request $request)
    {
        $param = $this->getQueryParameter($request, ["teacher_id" => 0,"semester_id" => 0]);
        $this->isNullData($param, ["teacher_id"]);
        
        $current_school_session_id = $this->getSchoolCurrentSession();

        $semesters = $this->semesterRepository->getAll($current_school_session_id);

        $assignedTeacherRepository = new AssignedTeacherRepository();

        if($param["semester_id"] == null) {
            $courses = [];
        } else {
            $courses = $assignedTeacherRepository->getTeacherCourses($current_school_session_id, $param["teacher_id"], $param["semester_id"]);
        }
        
        $data = [
            'courses'               => $courses,
            'semesters'             => $semesters,
            'selected_semester_id'  => $param["semester_id"],
        ];

        return view('courses.teacher', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  TeacherAssignRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TeacherAssignRequest $request)
    {
        try {
            $assignedTeacherRepository = new AssignedTeacherRepository();
            $assignedTeacherRepository->assign($request->validated());

            return back()->with('status', 'Assigning teacher was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
