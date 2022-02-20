<?php 
namespace App\Mediator;

interface Mediator{
    public function notify($repository, $function, ...$params);
}