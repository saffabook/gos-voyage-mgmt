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
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            $validatorErrorMessage = $validator->messages();
            return ApiResponse::error($validatorErrorMessage);
        }

        $post = Post::where('id', $id)->first();

        if (!$post) {
          return ApiResponse::error('Post not found');
        }

        $post->update([
          'title' => $request->input('title')
        ]);

        return ApiResponse::success($post, 'Post updated successfully');
    }
}
