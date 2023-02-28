<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Helpers\ApiResponse;

class UpdatePost extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        $validatorData = Validator::make($request->all(), [
            'title' => 'required|string|max:15',
        ]);

        if ($validatorData->fails()) {
            return ApiResponse::error($validatorData->messages());
        }

        $post = Post::where('id', $id)->first();

        if (!$post) {
          return ApiResponse::error('Post not found');
        }

        $post->update($validatorData->validated());

        return ApiResponse::success($post, 'Post updated successfully');
    }
}
