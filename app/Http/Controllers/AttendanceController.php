<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceStoreRequest;
use App\Mediator\Mediator;
use App\Mediator\MediatorAttendance;
use App\Repositories\AttendanceRepository;
use App\Template_Method\TemplateMethod;

class AttendanceController extends TemplateMethod
{
    protected Mediator $mediator;

    public function __construct() {
        $this->middleware(['can:view attendances']);
        $this->mediator = new MediatorAttendance();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return back();
        // $academic_setting = $this->academicSettingRepository->getAcademicSetting();

        // $current_school_session_id = $this->getSchoolCurrentSession();

        // $classes_and_sections = $this->schoolClassRepository->getClassesAndSections($current_school_session_id);
        // $courseRepository = new CourseRepository();
        // $courses = $courseRepository->getAll($current_school_session_id);

        // $data = [
        //     'academic_setting'      => $academic_setting,
        //     'classes_and_sections'  => $classes_and_sections,
        //     'courses'               => $courses,
        // ];

        // return view('attendances.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if($request->query('class_id') == null){
            return abort(404);
        }
        
        try{
            return view('attendances.take', $this->mediator->getData($this, "create", [
                "class_id" => $request->query('class_id'),
                "section_id" => $request->query('section_id', 0),
                "course_id" => $request->query('course_id')
            ]));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\AttendanceStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AttendanceStoreRequest $request)
    {
        try {
            $attendanceRepository = new AttendanceRepository();
            $attendanceRepository->saveAttendance($request->validated());

            return back()->with('status', 'Attendance save was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        if($request->query('class_id') == null){
            return abort(404);
        }
    
        try {
            return view('attendances.view', $this->mediator->getData($this, "show", [
                "class_id" => $request->query('class_id'),
                "section_id" => $request->query('section_id'),
                "course_id" => $request->query('course_id')
            ]));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function showStudentAttendance($id) {
        if(auth()->user()->role == "student" && auth()->user()->id != $id) {
            return abort(404);
        }

        return view('attendances.attendance', $this->mediator->getData($this,"show_student_attendace",["id" => $id]));
    }
}
