<?php 
namespace App\Decorator;

use App\Repositories\NoticeRepository;

class Message implements NotificationInterface{
    private string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function send_message(array $data = []){
        try {
            $noticeRepository = new NoticeRepository();
            $noticeRepository->store([
                'notice'        => $this->message,
                'session_id'    => $data['session_id'],
            ]);

            return [
                "error" => false,
                "message" => 'Creating Notice was successful!'
            ];
        } catch (\Exception $e) {
            return [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }
    }
}