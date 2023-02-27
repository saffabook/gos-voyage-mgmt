<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostComment;
use App\Helpers\ApiResponse;

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
          return ApiResponse::error('Comment not found');
        }

        $comment->delete();

        return ApiResponse::success($comment, 'Comment deleted successfully');
    }
}
