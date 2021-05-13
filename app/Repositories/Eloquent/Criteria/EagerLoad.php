<?php

namespace App\Repositories\Eloquent\Criteria;

use App\Repositories\Criteria\ICriterion;



class EagerLoad implements ICriterion
{
    //each time we insstaantiate this class we are going to 
    //passs this user_id to it
    protected $relationships;

    public function __construct($relationships)
    {
        $this->relationships = $relationships;
    }
    public function apply($model)
    {
        return $model->with($this->relationships);
    }
}
