<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mediator\Mediator;
use App\Traits\SchoolSession;
use App\Mediator\MediatorHome;

class HomeController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $userRepository;
    protected Mediator $mediator;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
        $this->mediator = new MediatorHome();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home', $this->mediator->getData($this,"index"));
    }
}
