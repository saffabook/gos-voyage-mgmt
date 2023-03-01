<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Helpers\ApiResponse;
use App\Models\PostComment;

class DeletePost extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id)
    {
        $post = Post::with('comments')->find($id);

        if (!$post) {
          return ApiResponse::error('Post not found');
        }

        $post->delete();
        PostComment::where('post_id', $id)->delete();

        return ApiResponse::success($post, 'Post deleted successfully');
    }
}
