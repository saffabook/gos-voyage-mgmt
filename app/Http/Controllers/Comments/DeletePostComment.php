<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostComment;

class DeletePostComment extends Controller
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
          return response()->json(['error' => 'Comment not found'], 404);
        } 

        $comment->delete();

        return response()->json(['data' => 'Comment deleted successfully'], 200);
    }
}
