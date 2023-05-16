<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Helpers\ApiResponse;

class GetPosts extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $postsFromDb = Post::get();

        if ($postsFromDb->isEmpty()) {
          return ApiResponse::error('No posts found');
        }

        return ApiResponse::success($postsFromDb->toArray());
    }
}
