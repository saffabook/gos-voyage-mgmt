<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostComment;

class GetPostComment extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id)
    {
        $comment = PostComment::where('id', $id)->first();

        if (!$comment) {
            return 'Comment does not exist';
        }

        return response()->json($comment);
    }
}
