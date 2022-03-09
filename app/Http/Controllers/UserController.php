<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Traits\StrategyContext;
use App\Interfaces\SectionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Repositories\PromotionRepository;
use App\Http\Requests\StudentStoreRequest;
use App\Http\Requests\TeacherStoreRequest;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\StudentParentInfoRepository;
use App\Strategy\ContextUserRepository;

class UserController extends Controller
{
    use SchoolSession, StrategyContext;
    
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $schoolSectionRepository;
    private ContextUserRepository $context;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $schoolSectionRepository)
    {
        $this->middleware(['can:view users']);
        $this->context = new ContextUserRepository();
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  TeacherStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeTeacher(TeacherStoreRequest $request)
    {
        $this->setStrategyContext(TEACHER);
        try {
            $this->context->executeCreate($request->validated());
            return back()->with('status', 'Teacher creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function getStudentList(Request $request) {
        $this->setStrategyContext(STUDENT);
        $current_school_session_id = $this->getSchoolCurrentSession();
        $class_id = $request->query('class_id', 0);
        $section_id = $request->query('section_id', 0);

        $data = [
            "session_id" => $current_school_session_id, 
            "class_id" => $class_id, 
            "section_id" => $section_id, 
        ];

        try{
            $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);
            $studentList = $this->context->executeGetAll($data);

            $data = [
                'studentList'       => $studentList,
                'school_classes'    => $school_classes,
            ];

            return view('students.list', $data);
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }


    public function showStudentProfile($id) {
        $this->setStrategyContext(STUDENT);
        $student = $this->context->executeFind($id);
        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotionRepository = new PromotionRepository();
        $promotion_info = $promotionRepository->getPromotionInfoById($current_school_session_id, $id);

        $data = [
            'student'           => $student,
            'promotion_info'    => $promotion_info,
        ];

        return view('students.profile', $data);
    }

    public function showTeacherProfile($id) {
        $this->setStrategyContext(TEACHER);
        $teacher = $this->context->executeFind($id);
        $data = [
            'teacher'   => $teacher,
        ];
        return view('teachers.profile', $data);
    }


    public function createStudent() {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'school_classes'            => $school_classes,
        ];

        return view('students.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StudentStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeStudent(StudentStoreRequest $request)
    {
        $this->setStrategyContext(STUDENT);
        try {
            $this->context->executeCreate($request->validated());
            return back()->with('status', 'Student creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function editStudent($student_id) {
        $this->setStrategyContext(STUDENT);

        $student = $this->context->executeFind($student_id);
        $studentParentInfoRepository = new StudentParentInfoRepository();
        $parent_info = $studentParentInfoRepository->getParentInfo($student_id);
        $promotionRepository = new PromotionRepository();
        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotion_info = $promotionRepository->getPromotionInfoById($current_school_session_id, $student_id);

        $data = [
            'student'       => $student,
            'parent_info'   => $parent_info,
            'promotion_info'=> $promotion_info,
        ];

        return view('students.edit', $data);
    }

    public function updateStudent(Request $request) {
        $this->setStrategyContext(STUDENT);
        try {
            $this->context->executeUpdate($request->toArray());
            return back()->with('status', 'Student update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function editTeacher($teacher_id) {
        $this->setStrategyContext(TEACHER);
        $teacher = $this->context->executeFind($teacher_id);

        $data = [
            'teacher'   => $teacher,
        ];

        return view('teachers.edit', $data);
    }

    public function updateTeacher(Request $request) {
        $this->setStrategyContext(TEACHER);
        try {
            $this->context->executeUpdate($request->toArray());
            return back()->with('status', 'Teacher update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function getTeacherList(){
        $this->setStrategyContext(TEACHER);
        $teachers = $this->context->executeGetAll();

        $data = [
            'teachers' => $teachers,
        ];

        return view('teachers.list', $data);
    }
}
