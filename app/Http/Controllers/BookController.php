<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

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

            $bookObject = (object) [
                'id' => ++$id,
                'title' => $book->name,
                'authors' => $book->authors, //this is an array containing author names
                'character' => $book->characters, //this is an array containing character urls
                'commentCount' => null,
                'comment' => array() //this is an array containing commentIds
            ];

            $response[] = $bookObject;
            
        }
        
        // Return response with above data
        return response()->json([
            'books' => $response
        ]);

    }
}
