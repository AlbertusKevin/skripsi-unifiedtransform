<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\PromotionRepository;
use App\Mediator\Mediator;
use App\Mediator\MediatorPromotion;

class PromotionController extends Controller
{
    protected Mediator $mediator;

    /**
    * Create a new Controller instance
    * 
    * @param SchoolSessionInterface $schoolSessionRepository
    * @return void
    */
    public function __construct() {
        $this->mediator = new MediatorPromotion();
    }
    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = $this->mediator->getData($this,"index",["class_id" => $request->query('class_id', 0)]);

        if(array_key_exists("error", $data)) {
            return back()->withError($data['error']);
        }

        return view('promotions.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $class_id = $request->query('previous_class_id');
        $section_id = $request->query('previous_section_id');
        $session_id = $request->query('previousSessionId');

        try{
            if($class_id == null || $section_id == null ||$session_id == null) {
                return abort(404);
            }

            $data = $this->mediator->getData($this, "create", [
                "class_id" => $class_id,
                "section_id" => $section_id,
                "session_id" => $session_id
            ]);

            return view('promotions.promote', $data);
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_card_numbers = $request->id_card_number;
        $latest_school_session = $this->schoolSessionRepository->getLatestSession();

        $rows = [];
        $i = 0;
        foreach($id_card_numbers as $student_id => $id_card_number) {
            $row = [
                'student_id'    => $student_id,
                'id_card_number'=> $id_card_number,
                'class_id'      => $request->class_id[$i],
                'section_id'    => $request->section_id[$i],
                'session_id'    => $latest_school_session->id,
            ];
            array_push($rows, $row);
            $i++;
        }

        try {
            $promotionRepository = new PromotionRepository();
            $promotionRepository->massPromotion($rows);

            return back()->with('status', 'Promoting students was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
