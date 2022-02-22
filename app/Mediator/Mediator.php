<?php 
namespace App\Mediator;

interface Mediator{
    public function notify($object, $event, $data = []);
}