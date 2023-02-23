<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostComment;
use Illuminate\Support\Facades\Validator;

class AddPostComment extends Controller
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
            'comment' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $validatorErrorMessage = $validator->messages();
            return response()->json(['error' => $validatorErrorMessage->toArray()], 404);
        }

        PostComment::create([
            'comment' => $request->input('comment')
        ]);

        return response()->json(['data' => 'The comment was added'], 200);
    }
}
