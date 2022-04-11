<?php 
namespace App\Mediator;

interface Mediator{
    public function getData($sender, $event, $data = []);
}