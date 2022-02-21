<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * List comments for a specific book
     *
     * @param String $bookId
     * @return JSON response
     */
    public function listComments($bookId) {
        return response()->json([
            'message' => 'Comments for bookId: ' . $bookId
        ]);
    }

    /**
     * Add comment for a specific book
     * 
     * @param Request $request
     * @return String response
     */
    public function addComment(Request $request) {
        if($request->filled('bookId') && $request->filled('comment')) {
            // Get ip address of the commenter
            // Add comment to db
            return response()->json([
                'message' =>'Comment added',
                'bookId' => $request->input('bookId'),
                'comment' => $request->input('comment')
            ]);
        }
        else {
            return response()->json([
                'error' => true,
                'message' => 'Missing required parameters'
            ]);
        }
        
    }
}
