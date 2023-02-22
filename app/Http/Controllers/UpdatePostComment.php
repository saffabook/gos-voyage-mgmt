<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostComment;

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
        $comment = PostComment::where('id', $id)->first();

        if (!$comment) {
          return 'Comment not found';
        }

        $comment->update([
          'comment' => $request->input('comment')
        ]);

        return 'Comment updated successfully';
    }
}
