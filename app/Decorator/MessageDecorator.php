<?php 

namespace App\Decorator;

class MessageDecorator implements NotificationInterface{
    protected NotificationInterface $wrappee;
    protected string $message;

    public function __construct(NotificationInterface $wrappee, string $message)
    {
        $this->wrappee = $wrappee;
        $this->message = $message;
    }

    public function send_message(array $data)
    {
        $this->wrappee->send_message($data);
    }
}