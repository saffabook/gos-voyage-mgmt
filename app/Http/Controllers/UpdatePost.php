<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            $validatorErrorMessage = $validator->messages();
            return response()->json(['error' => $validatorErrorMessage->toArray()], 404);
        }

        $post = Post::where('id', $id)->first();

        if (!$post) {
          return 'Post not found';
        }

        $post->update([
          'title' => $request->input('title')
        ]);

        return response()->json(['data' => 'Post updated successfully'], 200);
    }
}
