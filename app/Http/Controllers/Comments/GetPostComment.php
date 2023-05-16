<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostComment;
use App\Helpers\ApiResponse;

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
        $comment = PostComment::with('post')->find($id);

        if (!$comment) {
            return ApiResponse::error('Comment not found');
        }

        return ApiResponse::success($comment->toArray());
    }
}
