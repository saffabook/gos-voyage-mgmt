<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class AddPost extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $post = Post::create([
          'title' => $request->input('title')
        ]);

        return response()->json($post['title']);
    }
}
