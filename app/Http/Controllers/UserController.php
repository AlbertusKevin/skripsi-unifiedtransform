<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\UserInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Repositories\PromotionRepository;
use App\Http\Requests\StudentStoreRequest;
use App\Http\Requests\TeacherStoreRequest;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\StudentParentInfoRepository;
use App\Strategy\ConcreteStrategy\StudentStrategy;
use App\Strategy\ConcreteStrategy\TeacherStrategy;
use App\UserRepoStrategy\ContextUserRepository;
use App\UserRepoStrategy\UserRepoStrategy;

define("TEACHER", 'TEACHER');
define("STUDENT", 'STUDENT');

class UserController extends Controller
{
    use SchoolSession;
    
    protected $userRepository;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $schoolSectionRepository;
    private ContextUserRepository $context;

    public function __construct(UserInterface $userRepository, SchoolSessionInterface $schoolSessionRepository,
    SchoolClassInterface $schoolClassRepository,
    SectionInterface $schoolSectionRepository)
    {
        $this->middleware(['can:view users']);
        $this->context = new ContextUserRepository();

        $this->userRepository = $userRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
    }
    
    private function setStrategyContext($user){
        switch ($user) {
            case TEACHER:
                $this->context->setStrategy(new TeacherStrategy());
                break;
                
            default:
                $this->context->setStrategy(new StudentStrategy());
                break;
        }
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
        $current_school_session_id = $this->getSchoolCurrentSession();
        $class_id = $request->query('class_id', 0);
        $section_id = $request->query('section_id', 0);

        try{
            $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);
            $studentList = $this->userRepository->getAllStudents($current_school_session_id, $class_id, $section_id);

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
        $teacher = $this->userRepository->findTeacher($teacher_id);

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
        $teachers = $this->userRepository->getAllTeachers();

        $data = [
            'teachers' => $teachers,
        ];

        return view('teachers.list', $data);
    }
}
