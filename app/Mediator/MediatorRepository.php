<?php 
namespace App\Mediator;

use App\Http\Controllers\AcademicSettingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\PromotionController;
use App\Interfaces\AcademicSettingInterface;
use App\Interfaces\AssignedTeacherInterface;
use App\Interfaces\AttendanceInterface;
use App\Interfaces\CourseInterface;
use App\Interfaces\ExamInterface;
use App\Interfaces\MarkInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\SemesterInterface;
use App\Interfaces\UserInterface;
use App\Repositories\AcademicSettingRepository;
use App\Repositories\AssignedTeacherRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\CourseRepository;
use App\Repositories\ExamRepository;
use App\Repositories\GradeRuleRepository;
use App\Repositories\GradingSystemRepository;
use App\Repositories\MarkRepository;
use App\Repositories\NoticeRepository;
use App\Repositories\PromotionRepository;
use App\Repositories\SchoolClassRepository;
use App\Repositories\SchoolSessionRepository;
use App\Repositories\SectionRepository;
use App\Repositories\SemesterRepository;
use App\Repositories\UserRepository;
use App\Traits\AssignedTeacherCheck;
use App\Traits\SchoolSession;

class MediatorRepository implements Mediator{
    use SchoolSession, AssignedTeacherCheck;
    private AcademicSettingInterface $academicSettingRepository;
    private SchoolSessionInterface $schoolSessionRepository;
    private SchoolClassInterface $schoolClassRepository;
    private SectionInterface $schoolSectionRepository;
    private UserInterface $userRepository;
    private CourseInterface $courseRepository;
    private SemesterInterface $semesterRepository;
    private AttendanceInterface $attendanceRepository;
    private ExamInterface $examRepository;
    private AssignedTeacherInterface $assignedTeacherRepository;
    private PromotionRepository $promotionRepository;
    private NoticeRepository $noticeRepository;
    private GradingSystemRepository $gradeRulesRepository;
    private MarkRepository $markRepository;

    public function __construct()
    {
        $this->academicSettingRepository = new AcademicSettingRepository();
        $this->schoolSessionRepository = new SchoolSessionRepository();
        $this->schoolClassRepository = new SchoolClassRepository();
        $this->schoolSectionRepository = new SectionRepository();
        $this->userRepository = new UserRepository();
        $this->courseRepository = new CourseRepository();
        $this->semesterRepository = new SemesterRepository();
        $this->attendanceRepository = new AttendanceRepository();
        $this->examRepository = new ExamRepository();
        $this->assignedTeacherRepository = new AssignedTeacherRepository();
        $this->promotionRepository = new PromotionRepository();
        $this->noticeRepository = new NoticeRepository();
        $this->markRepository = new MarkRepository();
        $this->gradeRulesRepository = new GradingSystemRepository();
    }

    public function getData($sender, $event, $data = []){
        $school_session_id = $this->getSchoolCurrentSession();
        switch ($sender) {
            case $sender instanceof AcademicSettingController:
                if($event == "index"){
                    return [
                        'current_school_session_id' => $school_session_id,
                        'school_sessions'           => $this->schoolSessionRepository->getAll(),
                        'school_classes'            => $this->schoolClassRepository->getAllBySession($school_session_id),
                        'school_sections'           => $this->schoolSectionRepository->getAllBySession($school_session_id),
                        'teachers'                  => $this->userRepository->getAllTeachers(),
                        'courses'                   => $this->courseRepository->getAll($school_session_id),
                        'semesters'                 => $this->semesterRepository->getAll($school_session_id),
                        "latest_school_session_id"  => $this->schoolSessionRepository->getLatestSession()->id,
                        "academic_setting"          => $this->academicSettingRepository->getAcademicSetting()
                    ];
                }
                break;
            case $sender instanceof AttendanceController:
                if($event == "create"){
                    $academic_setting = $this->academicSettingRepository->getAcademicSetting();
                    $current_school_session_id = $this->getSchoolCurrentSession();
                    $attendance_count = $this->getAttendances($academic_setting->attendance_type,$data)->count();

                    return [
                        'current_school_session_id' => $current_school_session_id,
                        'academic_setting'  => $academic_setting,
                        'student_list'      => $this->userRepository->getAllStudents(
                                $current_school_session_id, 
                                $data["class_id"], 
                                $data["section_id"]
                            ),
                        'school_class'      => $this->schoolClassRepository->findById($data["class_id"]),
                        'school_section'    => $this->schoolSectionRepository->findById($data["section_id"]),
                        'attendance_count'  => $attendance_count,
                    ];
                }
                
                if($event == "show"){
                    $academic_setting = $this->academicSettingRepository->getAcademicSetting();
                    $data = array_merge(["current_school_session_id" => $this->getSchoolCurrentSession()],$data);
                    return ["attendances" => $this->getAttendances($academic_setting->attendance_type, $data)];
                }

                if($event == "show_student_attendace"){
                    return [
                        'attendances'   => $this->attendanceRepository->getStudentAttendance($this->getSchoolCurrentSession(), $data["id"]),
                        'student'       => $this->userRepository->findStudent($data["id"]),
                    ];
                }
                break;
            case $sender instanceof ExamController:
                if($event == "index"){
                    $current_school_session_id = $this->getSchoolCurrentSession();

                    return [
                        'current_school_session_id' => $current_school_session_id,
                        'semesters'                 => $this->semesterRepository->getAll($current_school_session_id),
                        'classes'                   => $this->schoolClassRepository->getAllBySession($current_school_session_id),
                        'exams'                     => $this->examRepository->getAll(
                                $current_school_session_id, $data["semester_id"], $data["class_id"]
                            ),
                        'teacher_courses'           => $this->assignedTeacherRepository->getTeacherCourses(
                                $current_school_session_id, $data["teacher_id"], $data["semester_id"]
                            )
                    ];
                }

                if($event == "create"){
                    $current_school_session_id = $this->getSchoolCurrentSession();
                    $semesters = $this->semesterRepository->getAll($current_school_session_id);

                    if(auth()->user()->role == "teacher") {
                        $school_classes = $this->getTeacherSchoolClasses($current_school_session_id);
                    } else {
                        $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);
                    }

                    return [
                        'current_school_session_id' => $current_school_session_id,
                        'semesters'                 => $semesters,
                        'classes'                   => $school_classes,
                    ];
                }
                break;
            case $sender instanceof HomeController:
                if($event == "index"){
                    $current_school_session_id = $this->getSchoolCurrentSession();
                    return [
                        'classCount'    => $this->schoolClassRepository->getAllBySession($current_school_session_id)->count(),
                        'studentCount'  => $this->userRepository->getAllStudentsBySessionCount($current_school_session_id),
                        'teacherCount'  => $this->userRepository->getAllTeachers()->count(),
                        'notices'       => $this->noticeRepository->getAll($current_school_session_id),
                        'maleStudentsBySession' => $this->promotionRepository->getMaleStudentsBySessionCount($current_school_session_id),
                    ];
                }
            case $sender instanceof MarkController:
                if($event == "index"){
                    $current_school_session_id = $this->getSchoolCurrentSession();
                    $semesters = $this->semesterRepository->getAll($current_school_session_id);
                    $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);
                    $marks = $this->markRepository->getAllFinalMarks($current_school_session_id, $data["semester_id"], $data["class_id"], $data["section_id"], $data["course_id"]);
                    $gradingSystem = $this->gradingSystemRepository->getGradingSystem($current_school_session_id, $data["semester_id"], $data["class_id"]);
                    $gradingSystemRules = (!$gradingSystem) ? null : $this->gradeRulesRepository->getAll($current_school_session_id, $gradingSystem->id);

                    return [
                        'current_school_session_id' => $current_school_session_id,
                        'semesters'                 => $semesters,
                        'classes'                   => $school_classes,
                        'marks'                     => $marks,
                        'grading_system_rules'      => $gradingSystemRules,
                    ];
                }

                if($event == "create"){
                    $current_school_session_id = $this->getSchoolCurrentSession();
                    $this->checkIfLoggedInUserIsAssignedTeacher($data["request"], $current_school_session_id);
                    $academic_setting = $this->academicSettingRepository->getAcademicSetting();
                    $exams = $this->examRepository->getAll($current_school_session_id, $data["semester_id"], $data["class_id"]);
                    $studentsWithMarks = $this->markRepository
                            ->getAll($current_school_session_id, $data["semester_id"], $data["class_id"], $data["section_id"], $data["course_id"])
                            ->groupBy('student_id');
                    $sectionStudents = $this->userRepository->getAllStudents($current_school_session_id, $data["class_id"], $data["section_id"]);
                    $final_marks_submit_count = $this->markRepository->getFinalMarksCount($current_school_session_id, $data["semester_id"], $data["class_id"], $data["section_id"], $data["course_id"]);
                    $final_marks_submitted = false;

                    if($final_marks_submit_count > 0) {
                        $final_marks_submitted = true;
                    }

                    return [
                        'academic_setting'          => $academic_setting,
                        'exams'                     => $exams,
                        'students_with_marks'       => $studentsWithMarks,
                        'class_id'                  => $data["class_id"],
                        'section_id'                => $data["section_id"],
                        'course_id'                 => $data["course_id"],
                        'semester_id'               => $data["semester_id"],
                        'final_marks_submitted'     => $final_marks_submitted,
                        'sectionStudents'           => $sectionStudents,
                        'current_school_session_id' => $current_school_session_id,
                    ];
                }

                if($event == "show_course_mark"){
                    $marks = $this->markRepository->getAllByStudentId($data["session_id"], $data["semester_id"], $data["class_id"], $data["section_id"], $data["course_id"], $data["student_id"]);
                    $finalMarks = $this->markRepository->getAllFinalMarksByStudentId($data["session_id"], $data["student_id"], $data["semester_id"], $data["class_id"], $data["section_id"], $data["course_id"]);
                    $gradingSystem = $this->gradingSystemRepository->getGradingSystem($data["session_id"], $data["semester_id"], $data["class_id"]);
                    $gradingSystemRules = (!$gradingSystem) ? null : $this->gradeRulesRepository->getAll($data["session_id"], $gradingSystem->id);

                    return [
                        "marks" => $marks,
                        "finalMarks" => $finalMarks,
                        "gradingSystemRules" => $gradingSystemRules,
                        'course_name' => $data["course_name"],
                    ];
                }
            case $sender instanceof PromotionController:
                if($event == "index"){
                    $previousSession = $this->schoolSessionRepository->getPreviousSession();

                    if(count($previousSession) < 1) {
                        return ["error" => true];
                    }

                    $previousSessionClasses = $this->promotionRepository->getClasses($previousSession['id']);
                    $previousSessionSections = $this->promotionRepository->getSections($previousSession['id'], $data["class_id"]);
                    $current_school_session_id = $this->getSchoolCurrentSession();
                    $currentSessionSections = $this->promotionRepository->getSectionsBySession($current_school_session_id);
                    $currentSessionSectionsCounts = $currentSessionSections->count();

                    return [
                        'previousSessionClasses'        => $previousSessionClasses,
                        'class_id'                      => $data["class_id"],
                        'previousSessionSections'       => $previousSessionSections,
                        'currentSessionSectionsCounts'  => $currentSessionSectionsCounts,
                        'previousSessionId'             => $previousSession['id'],
                    ];
                }

                if($event == "create"){
                    $students = $this->userRepository->getAllStudents($data["session_id"], $data["class_id"], $data["section_id"]);
                    $schoolClass = $this->schoolClassRepository->findById($data["class_id"]);
                    $section = $this->schoolSectionRepository->findById($data["section_id"]);
                    $latest_school_session = $this->schoolSessionRepository->getLatestSession();
                    $school_classes = $this->schoolClassRepository->getAllBySession($latest_school_session->id);

                    return [
                        'students'      => $students,
                        'schoolClass'   => $schoolClass,
                        'section'       => $section,
                        'school_classes'=> $school_classes,
                    ];
                }
            default:
                break;
        }
        
    }

    private function getAttendances(string $attendance_type, array $data){
        if($attendance_type == 'section') {
            return $this->attendanceRepository->getSectionAttendance($data["class_id"], $data["section_id"], $data["current_school_session_id"]);
        }

        return $this->attendanceRepository->getCourseAttendance($data["class_id"], $data["course_id"], $data["current_school_session_id"]);
    }

    private function getTeacherSchoolClasses($current_school_session_id){
        $teacher_id = auth()->user()->id;
        $assigned_classes = $this->schoolClassRepository->getAllBySessionAndTeacher($current_school_session_id, $teacher_id);

        $school_classes = [];
        $i = 0;

        foreach($assigned_classes as $assigned_class) {
            $school_classes[$i] = $assigned_class->schoolClass;
            $i++;
        }

        return $school_classes;
    }
}