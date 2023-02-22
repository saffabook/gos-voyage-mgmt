<?php

namespace App\Http\Controllers;

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
          return 'Post not found';
        } 

        $comment->delete();

        return 'Post deleted successfully';
    }
}
