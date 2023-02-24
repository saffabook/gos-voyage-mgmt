<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Helpers\ApiResponse;

class GetPost extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id)
    {
        $post = Post::where('id', $id)->first();

        if (!$post) {
            return 'Post does not exist';
        }

        return ApiResponse::success($post);
    }
}
