<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamStoreRequest;
use App\Models\Exam;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\SemesterInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Mediator\MediatorRepository;
use App\Repositories\AssignedTeacherRepository;
use App\Repositories\ExamRepository;

class ExamController extends Controller
{
    use SchoolSession;

    protected $schoolClassRepository;
    protected $semesterRepository;
    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository, SchoolClassInterface $schoolClassRepository, SemesterInterface $semesterRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->semesterRepository = $semesterRepository;
        $this->mediator = new MediatorRepository();
    }
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('exams.index', $this->mediator->getData($this, "index", [
            "class_id" => $request->query('class_id', 0),
            "semester_id" => $request->query('semester_id', 0),
            "teacher_id" => (auth()->user()->role == "teacher")?auth()->user()->id : 0
        ]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('exams.create', $this->mediator->getData($this, "create"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ExamStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ExamStoreRequest $request)
    {
        try {
            $examRepository = new ExamRepository();
            $examRepository->create($request->validated());

            return back()->with('status', 'Exam creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Exam  $exam
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $examRepository = new ExamRepository();
            $examRepository->delete($request->exam_id);

            return back()->with('status', 'Exam deletion was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
