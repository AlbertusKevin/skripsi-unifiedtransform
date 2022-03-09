<?php 
namespace App\UserRepoStrategy;

class ContextUserRepository{
    private UserRepoStrategy $strategy;

    public function setStrategy(UserRepoStrategy $strategy){
        $this->strategy = $strategy;
    }

    public function executeCreate($request){
        $this->strategy->create($request);
    }

    public function executeUpdate($request){
        $this->strategy->update($request);
    }

    public function executeFind($id){
        $this->strategy->find($id);
    }
}