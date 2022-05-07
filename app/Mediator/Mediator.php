<?php 
namespace App\Mediator;

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
use App\Strategy\ContextUserRepository;
use App\Traits\AssignedTeacherCheck;
use App\Traits\SchoolSession;
use App\Traits\StrategyContext;

// define("TEACHER", 'TEACHER');
// define("STUDENT", 'STUDENT');

abstract class Mediator{
    use SchoolSession, AssignedTeacherCheck, StrategyContext;
    protected AcademicSettingRepository $academicSettingRepository;
    protected SchoolSessionRepository $schoolSessionRepository;
    protected SchoolClassRepository $schoolClassRepository;
    protected SectionRepository $schoolSectionRepository;
    protected ContextUserRepository $context;
    protected CourseRepository $courseRepository;
    protected SemesterRepository $semesterRepository;
    protected AttendanceRepository $attendanceRepository;
    protected ExamRepository $examRepository;
    protected AssignedTeacherRepository $assignedTeacherRepository;
    protected PromotionRepository $promotionRepository;
    protected NoticeRepository $noticeRepository;
    protected GradingSystemRepository $gradeSystemRepository;
    protected GradeRuleRepository $gradeRulesRepository;
    protected MarkRepository $markRepository;
    protected $school_session_id;
    protected $academic_setting;

    public function __construct()
    {
        $this->academicSettingRepository = new AcademicSettingRepository();
        $this->schoolSessionRepository = new SchoolSessionRepository();
        $this->schoolClassRepository = new SchoolClassRepository();
        $this->schoolSectionRepository = new SectionRepository();
        $this->courseRepository = new CourseRepository();
        $this->semesterRepository = new SemesterRepository();
        $this->attendanceRepository = new AttendanceRepository();
        $this->examRepository = new ExamRepository();
        $this->assignedTeacherRepository = new AssignedTeacherRepository();
        $this->promotionRepository = new PromotionRepository();
        $this->noticeRepository = new NoticeRepository();
        $this->markRepository = new MarkRepository();
        $this->gradeSystemRepository = new GradingSystemRepository();
        $this->gradeRulesRepository = new GradeRuleRepository();
        $this->context = new ContextUserRepository();

        $this->school_session_id = $this->getSchoolCurrentSession();
        $this->academic_setting = $this->academicSettingRepository->getAcademicSetting();
    }
    
    abstract public function getData($sender, $event, $data = []);
}