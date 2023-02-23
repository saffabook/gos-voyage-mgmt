<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostComment;
use Illuminate\Support\Facades\Validator;

class UpdatePostComment extends Controller
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
            'comment' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $validatorErrorMessage = $validator->messages();
            return response()->json(['error' => $validatorErrorMessage->toArray()], 404);
        }

        $comment = PostComment::where('id', $id)->first();

        if (!$comment) {
          return response()->json(['error' => 'Comment not found'], 404);
        }

        $comment->update([
          'comment' => $request->input('comment')
        ]);

        return response()->json(['data' => 'Comment updated successfully'], 200);
    }
}
