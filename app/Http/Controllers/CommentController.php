<?php

namespace App\Http\Controllers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommentController extends Controller
{
    /**
     * Fetch comments for a specific book from the comments table in the db
     *
     * @param String $bookId
     * @return JSON comments
     */
    public function listComments($bookId) {

        $comments = DB::select('select * from comments where bookId = ?', [$bookId]);

        return response()->json([
            'comments' => $comments,
            'commentCount' => count($comments)
        ]);
    }

    /**
     * Add comment for a specific book
     * 
     * @param Request $request
     * @return String message
     */
    public function addComment(Request $request) {

        if($request->filled('bookId') && $request->filled('comment')) {

            // If the comments table exists then insert comment
            if (Schema::hasTable('comments')) {
                
                $response = $this->insertComment(
                    $request->input('bookId'),
                    $request->ip(),
                    $request->input('comment'),
                    date(DATE_RFC850) // returns UTC date time
                );

            }
            // If comments table doesn't exist then create it and then insert comment
            else {

                Schema::create('comments', function(Blueprint $table) {
                    $table->id();
                    $table->string('bookId', 7);
                    $table->string('ipAddress', 45);
                    $table->string('comment', 500);
                    $table->string('date', 50);
                });

                $response = $this->insertComment(
                    $request->input('bookId'),
                    $request->ip(),
                    $request->input('comment'),
                    date(DATE_RFC850)
                );

            }

            return response()->json([
                'message' => $response ? 'Comment added' : 'Unable to add comment'
            ]);
        }
        else {
            return response()->json([
                'error' => true,
                'message' => 'Missing required parameters: bookId & comment'
            ]);
        }
        
    }

    /**
     * Inserts comment into the comment table
     *
     * @param string $bookId
     * @param string $ip
     * @param string $comment
     * @param string $time
     * @return bool
     */
    private function insertComment($bookId, $ip, $comment, $time) {
        return DB::insert('insert into comments (bookId, ipAddress, comment, date) values (?, ?, ?, ?)', 
            [
                $bookId,
                $ip,
                $comment,
                $time
            ]
        );
    }
}
