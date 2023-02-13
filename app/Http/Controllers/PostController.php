<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function storeNewPost(Request $request)
    {
        $incomingFields = $request->validate([
            'title' => 'required',
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);

        Post::create($incomingFields);

        return 'something';
    }

    public function displayPost()
    {
        return 'post';
    }
}
