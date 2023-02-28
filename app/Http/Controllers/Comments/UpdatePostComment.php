<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostComment;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;

class UpdatePostComment extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        $validatedData = Validator::make($request->all(), [
            'comment' => 'required|string|max:255',
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $comment = PostComment::where('id', $id)->first();

        if (!$comment) {
            return ApiResponse::error('Comment not found');
        }

        $comment->update($validatedData->validated());

        return ApiResponse::success($comment, 'Comment updated successfully');
    }
}
