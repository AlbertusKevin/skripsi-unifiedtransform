<?php 

namespace App\Decorator;

interface NotificationInterface{
    function send_message(array $data);
}
