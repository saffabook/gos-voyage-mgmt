<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
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
          return response()->json(['error' => 'No comments found'], 404);
        }

        return response()->json($commentsFromDb);
    }
}
