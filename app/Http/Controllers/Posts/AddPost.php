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
        $validatorData = Validator::make($request->all(), [
            'title' => 'required|string|max:15',
        ]);

        if ($validatorData->fails()) {
            return ApiResponse::error($validatorData->messages());
        }

        $title = Post::create($validatorData->validated());

        return ApiResponse::success($title, 'The post was added');
    }
}
