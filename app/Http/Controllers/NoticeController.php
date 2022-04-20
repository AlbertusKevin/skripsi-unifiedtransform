<?php

namespace App\Http\Controllers;

use App\Decorator\EmailDecorator;
use App\Decorator\Message;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Repositories\NoticeRepository;
use App\Http\Requests\NoticeStoreRequest;
use App\Interfaces\SchoolSessionInterface;

class NoticeController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository) {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        return view('notices.create', compact('current_school_session_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  NoticeStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NoticeStoreRequest $request)
    {
        $request->validated();
        $notice = new Message($request["notice"]);
        $send_notice = $notice->send_message(["session_id" => $request['session_id']]);
        
        $email = new EmailDecorator($notice, $request["notice"]);
        $email->send_message();

        if(!$send_notice["error"]){
            return back()->with('status', $send_notice["message"]);
        }

        return back()->withError($send_notice["message"]);
    }
}
