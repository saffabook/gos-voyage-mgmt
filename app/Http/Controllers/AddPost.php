<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;

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
            $validatorErrorMessage = $validator->messages();
            return response()->json(['error' => $validatorErrorMessage->toArray()], 404);
        }

        Post::create([
            'title' => $request->input('title')
        ]);

        return response()->json(['data' => 'The post was added'], 200);
    }
}
