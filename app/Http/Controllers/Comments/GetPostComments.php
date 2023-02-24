<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostComment;
use App\Helpers\ApiResponse;

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
          return ApiResponse::error('No comments found');
        }

        return ApiResponse::success($commentsFromDb);
    }
}
