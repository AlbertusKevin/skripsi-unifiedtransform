<?php 

namespace App\Decorator;

use App\Mail\PrepEmail;
use Illuminate\Support\Facades\Mail;

class EmailDecorator extends MessageDecorator{
    public function send_message(array $data = [])
    {
        $details = [
            'message' => $this->message
        ];
        
        Mail::to('if-18020@students.ithb.ac.id')->send(new PrepEmail($details));
    }
}