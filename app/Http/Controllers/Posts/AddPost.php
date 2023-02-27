<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Helpers\ApiResponse;

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
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->messages());
        }

        $title = Post::create([
            'title' => $request->input('title')
        ]);

        return ApiResponse::success($title, 'The post was added');
    }
}
