<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostComment;

class GetPostComments extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $commentsFromDb = PostComment::all();

        if ($commentsFromDb->isEmpty()) {
          return 'There are no comments';
        }

        return response()->json($commentsFromDb);
    }
}
