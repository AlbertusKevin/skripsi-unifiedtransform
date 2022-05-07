<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\AcademicSettingInterface;
use App\Http\Requests\AttendanceTypeUpdateRequest;
use App\Mediator\Mediator;
use App\Mediator\MediatorAcademicSetting;

class AcademicSettingController extends Controller
{
    protected $academicSettingRepository;
    protected Mediator $mediator;

    public function __construct(
        AcademicSettingInterface $academicSettingRepository
    ) {
        $this->middleware(['can:view academic settings']);
        $this->mediator = new MediatorAcademicSetting();
        $this->academicSettingRepository = $academicSettingRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('academics.settings', $this->mediator->getData($this,"index"));
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
