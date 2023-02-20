<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

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
        $post = Post::where('id', $id)->first();

        if (!$post) {
          return 'Post not found';
        }

        $request->validate([
            'title' => 'required|string|max:15'
        ]);

        $post->update([
          'title' => $request->input('title')
        ]);

        return 'Post updated successfully';
    }
}
