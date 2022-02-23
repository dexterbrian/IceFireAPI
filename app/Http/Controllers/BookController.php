<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * Get book data from external API.
     * 
     * @return JSON response
     */
    public function listBooks() {
        
        // Get all books from external API
        $externalApiResponse = Http::get($this->apiUrl . 'books');

        // Get id, title, authors, commentCount, commentids, characterids from the book data
        $booksArray = json_decode($externalApiResponse->body());
        $id = 0;

        // Loop through the array and get the id, title/name, authors (array), commentCount, commentids, characterids from the book data
        foreach ($booksArray as $book) {

            $id = ++$id;

            $comments = $this->getComments($id);

            $bookObject = (object) [
                'id' => $id,
                'title' => $book->name,
                'authors' => $book->authors, //this is an array containing author names
                'commentCount' => $comments->commentCount,
                'comments' => $comments->comments, //this is an array containing commentIds
                'characters' => $book->characters //this is an array containing character urls
            ];

            $response[] = $bookObject;
            
        }
        
        // Return response with above data
        return response()->json([
            'books' => $response
        ]);

    }

    /**
     * Get a book's comments and comment count
     *
     * @param int $bookId
     * @return object commentCount
     */
    private function getComments($bookId) {

        $comments = DB::select('select * from comments where bookId = ?', [$bookId]);

        $commentIds = array();

        foreach ($comments as $comment) {
            $commentIds[] = $comment->id;
        }

        return (object) [
            'comments' => $commentIds,
            'commentCount' => count($comments)
        ];
    }
}
