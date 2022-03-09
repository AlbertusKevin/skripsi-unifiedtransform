<?php 
namespace App\Mediator;

use App\Interfaces\AcademicSettingInterface;
use App\Interfaces\CourseInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\SemesterInterface;
use App\Interfaces\UserInterface;
use App\Repositories\AcademicSettingRepository;
use App\Repositories\CourseRepository;
use App\Repositories\SchoolClassRepository;
use App\Repositories\SchoolSessionRepository;
use App\Repositories\SectionRepository;
use App\Repositories\SemesterRepository;
use App\Repositories\UserRepository;

class MediatorRepository implements Mediator{
    private AcademicSettingInterface $academicSettingRepository;
    private SchoolSessionInterface $schoolSessionRepository;
    private SchoolClassInterface $schoolClassRepository;
    private SectionInterface $schoolSectionRepository;
    private UserInterface $userRepository;
    private CourseInterface $courseRepository;
    private SemesterInterface $semesterRepository;

    public function __construct()
    {
        $this->academicSettingRepository = new AcademicSettingRepository();
        $this->schoolSessionRepository = new SchoolSessionRepository();
        $this->schoolClassRepository = new SchoolClassRepository();
        $this->schoolSectionRepository = new SectionRepository();
        $this->userRepository = new UserRepository();
        $this->courseRepository = new CourseRepository();
        $this->semesterRepository = new SemesterRepository();
    }

    public function getData($object, $event, $data = []){
        if($event == "index"){
            return [
                'current_school_session_id' => $data["school_session_id"],
                'school_sessions'           => $this->schoolSessionRepository->getAll(),
                'school_classes'            => $this->schoolClassRepository->getAllBySession($data["school_session_id"]),
                'school_sections'           => $this->schoolSectionRepository->getAllBySession($data["school_session_id"]),
                'teachers'                  => $this->userRepository->getAllTeachers(),
                'courses'                   => $this->courseRepository->getAll($data["school_session_id"]),
                'semesters'                 => $this->semesterRepository->getAll($data["school_session_id"]),
                "latest_school_session_id"  => $this->schoolSessionRepository->getLatestSession(),
                "academic_setting"          => $this->academicSettingRepository->getAcademicSetting()
            ];
        }
    }
}