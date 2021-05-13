<?php

namespace App\Repositories\Eloquent\Criteria;

use App\Repositories\Criteria\ICriterion;



class ForUser implements ICriterion
{
    //each time we insstaantiate this class we are going to 
    //passs this user_id to it
    protected $user_id;

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }
    public function apply($model)
    {
        return $model->where('user_id', $this->user_id);
    }
}
