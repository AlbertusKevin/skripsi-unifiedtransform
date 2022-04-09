<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\SchoolSession;
use App\Repositories\NoticeRepository;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\PromotionRepository;
use App\Strategy\ContextUserRepository;
use App\Traits\StrategyContext;

class HomeController extends Controller
{
    use SchoolSession, StrategyContext;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $userRepository;
    private ContextUserRepository $context;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        SchoolSessionInterface $schoolSessionRepository, SchoolClassInterface $schoolClassRepository)
    {
        // $this->middleware('auth');
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->context = new ContextUserRepository();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $classCount = $this->schoolClassRepository->getAllBySession($current_school_session_id)->count();

        $data = [
            "session_id" => $current_school_session_id,
            "by_session_count" => true
        ];
        
        $this->setStrategyContext(STUDENT);
        $studentCount = $this->context->executeGetAll($data);
        
        $promotionRepository = new PromotionRepository();
        $maleStudentsBySession = $promotionRepository->getMaleStudentsBySessionCount($current_school_session_id);
        
        $this->setStrategyContext(TEACHER);
        $teacherCount = $this->context->executeGetAll()->count();

        $noticeRepository = new NoticeRepository();
        $notices = $noticeRepository->getAll($current_school_session_id);

        $data = [
            'classCount'    => $classCount,
            'studentCount'  => $studentCount,
            'teacherCount'  => $teacherCount,
            'notices'       => $notices,
            'maleStudentsBySession' => $maleStudentsBySession,
        ];

        return view('home', $data);
    }
}
