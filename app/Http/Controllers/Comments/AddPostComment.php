<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PostComment;
use App\Helpers\ApiResponse;

class AddPostComment extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $validatorData = Validator::make($request->all(), [
            'comment' => 'required|string|max:255',
            'post_id' => 'required|exists:posts,id'
        ]);

        if ($validatorData->fails()) {
            return ApiResponse::error($validatorData->messages());
        }

        $comment = PostComment::create($validatorData->validated());

        return ApiResponse::success($comment, 'The comment was added');
    }
}
