<?php

namespace App\Http\Controllers\Designs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\IComment;

class CommentController extends Controller
{
    //

    protected $comments;

    public function __construct(IComment $comments)
    {
        $this->comments = $comments;
    }

    public function store(Request $request, $designId)
    {
        $this->validate($request, [
            'body' => ['required']
        ]);
    }
}
