<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\CourseInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\SemesterInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\AcademicSettingInterface;
use App\Http\Requests\AttendanceTypeUpdateRequest;
use App\Traits\StrategyContext;
use App\Strategy\ContextUserRepository;

class AcademicSettingController extends Controller
{
    use SchoolSession, StrategyContext;
    protected $academicSettingRepository;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $schoolSectionRepository;
    protected $courseRepository;
    protected $semesterRepository;
    private ContextUserRepository $context;

    public function __construct(
        AcademicSettingInterface $academicSettingRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $schoolSectionRepository,
        CourseInterface $courseRepository,
        SemesterInterface $semesterRepository
    ) {
        $this->middleware(['can:view academic settings']);

        $this->context = new ContextUserRepository();
        $this->academicSettingRepository = $academicSettingRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
        $this->courseRepository = $courseRepository;
        $this->semesterRepository = $semesterRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $latest_school_session = $this->schoolSessionRepository->getLatestSession();
        $academic_setting = $this->academicSettingRepository->getAcademicSetting();
        $school_sessions = $this->schoolSessionRepository->getAll();
        $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);
        $school_sections = $this->schoolSectionRepository->getAllBySession($current_school_session_id);
        $courses = $this->courseRepository->getAll($current_school_session_id);
        $semesters = $this->semesterRepository->getAll($current_school_session_id);

        $this->setStrategyContext(TEACHER);
        $teachers = $this->context->executeGetAll();

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'latest_school_session_id'  => $latest_school_session->id,
            'academic_setting'          => $academic_setting,
            'school_sessions'           => $school_sessions,
            'school_classes'            => $school_classes,
            'school_sections'           => $school_sections,
            'teachers'                  => $teachers,
            'courses'                   => $courses,
            'semesters'                 => $semesters,
        ];

        return view('academics.settings', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  AttendanceTypeUpdateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function updateAttendanceType(AttendanceTypeUpdateRequest $request)
    {
        try {
            $this->academicSettingRepository->updateAttendanceType($request->validated());

            return back()->with('status', 'Attendance type update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function updateFinalMarksSubmissionStatus(Request $request) {
        try {
            $this->academicSettingRepository->updateFinalMarksSubmissionStatus($request);

            return back()->with('status', 'Final marks submission status update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
