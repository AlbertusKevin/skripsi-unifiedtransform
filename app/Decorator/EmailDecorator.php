<?php 

namespace App\Decorator;

use App\Mail\PrepEmail;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Mail;

class EmailDecorator extends MessageDecorator{
    public function send_message(array $data = [])
    {
        $details = [
            'message' => $this->message
        ];

        $userRepo = new UserRepository();
        $users = $userRepo->getAllStudents($data["session_id"], $data["class_id"], $data["section_id"]);

        foreach($users as $user){
            Mail::to($user->email)->send(new PrepEmail($details));
        }
        
    }
}