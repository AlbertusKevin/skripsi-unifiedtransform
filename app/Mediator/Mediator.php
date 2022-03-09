<?php 
namespace App\Mediator;

interface Mediator{
    public function getData($object, $event, $data = []);
}