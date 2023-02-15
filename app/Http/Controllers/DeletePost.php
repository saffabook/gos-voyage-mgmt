<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

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
        $post = Post::where('id', $id)->first();
        
        if (!$post) {
          return 'Post not found';
        } 

        $post->delete();

        return 'Post deleted successfully';
    }
}
