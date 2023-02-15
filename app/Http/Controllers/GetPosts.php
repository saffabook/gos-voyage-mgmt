<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

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

        if (!$postsFromDb) {
          return 'There are no posts';
        }

        return response()->json($postsFromDb);
    }
}