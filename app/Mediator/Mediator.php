<?php 
namespace App\Mediator;

use App\Repositories\AcademicSettingRepository;
use App\Repositories\AssignedTeacherRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\CourseRepository;
use App\Repositories\ExamRepository;
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

abstract class Mediator{
    use SchoolSession, AssignedTeacherCheck;
    protected AcademicSettingRepository $academicSettingRepository;
    protected SchoolSessionRepository $schoolSessionRepository;
    protected SchoolClassRepository $schoolClassRepository;
    protected SectionRepository $schoolSectionRepository;
    protected UserRepository $userRepository;
    protected CourseRepository $courseRepository;
    protected SemesterRepository $semesterRepository;
    protected AttendanceRepository $attendanceRepository;
    protected ExamRepository $examRepository;
    protected AssignedTeacherRepository $assignedTeacherRepository;
    protected PromotionRepository $promotionRepository;
    protected NoticeRepository $noticeRepository;
    protected GradingSystemRepository $gradeRulesRepository;
    protected MarkRepository $markRepository;

    protected $school_session_id = $this->getSchoolCurrentSession();
    protected $academic_setting = $this->academicSettingRepository->getAcademicSetting();

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
    
    abstract public function getData($sender, $event, $data = []);
}