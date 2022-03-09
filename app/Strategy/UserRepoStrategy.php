<?php 
namespace App\UserRepoStrategy;

abstract class UserRepoStrategy{
    public function create($request){}
    public function update($request){}
    public function find($id){}
}