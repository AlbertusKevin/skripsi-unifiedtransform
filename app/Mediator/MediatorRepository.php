<?php 
namespace App\Mediator;

use App\Http\Controllers\AcademicSettingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ExamController;
use App\Interfaces\AcademicSettingInterface;
use App\Interfaces\AssignedTeacherInterface;
use App\Interfaces\AttendanceInterface;
use App\Interfaces\CourseInterface;
use App\Interfaces\ExamInterface;
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
use App\Repositories\SchoolClassRepository;
use App\Repositories\SchoolSessionRepository;
use App\Repositories\SectionRepository;
use App\Repositories\SemesterRepository;
use App\Repositories\UserRepository;
use App\Traits\SchoolSession;

class MediatorRepository implements Mediator{
    use SchoolSession;
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
                        $teacher_id = auth()->user()->id;
                        $assigned_classes = $this->schoolClassRepository->getAllBySessionAndTeacher($current_school_session_id, $teacher_id);

                        $school_classes = [];
                        $i = 0;

                        foreach($assigned_classes as $assigned_class) {
                            $school_classes[$i] = $assigned_class->schoolClass;
                            $i++;
                        }
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
}