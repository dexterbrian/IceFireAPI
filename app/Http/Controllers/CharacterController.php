<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CharacterController extends Controller
{

    /**
     * Lists all characters. You can also sort them by name and age and filter by gender
     * 
     * @param Request $request
     * @return JSON response
     */
    public function listAllCharacters(Request $request) {

        // Validate request parameters
        if ($request->filled('sortType') && $request->input('sortType') != 'age' && $request->input('sortType') != 'name') {
            return response()->json([
                'error' => true,
                'message' => 'Invalid sortType'
            ]);
        }

        if ($request->filled('sortOrder') && $request->input('sortOrder') != 'asc' && $request->input('sortOrder') != 'des') {
            return response()->json([
                'error' => true,
                'message' => 'Invalid sortOrder'
            ]);
        }

        if ($request->filled('sortType') && empty($request->input('sortOrder'))) {
            return response()->json([
                'error' => true,
                'message' => 'sortOrder required'
            ]);
        }

        if ($request->filled('sortOrder') && empty($request->input('sortType'))) {
            return response()->json([
                'error' => true,
                'message' => 'sortType required'
            ]);
        }

        if ($request->filled('filter') && $request->input('filter') != 'Male' && $request->input('filter') != 'Female') {
            return response()->json([
                'error' => true,
                'message' => 'Invalid filter'
            ]);
        }

        // STEPS:
        // Get all characters from the external API and return all characters as JSON response
        $externalApiResponse = Http::get($this->apiUrl . 'characters');

        // Enable filtering by gender and returning the filtered characters as JSON response
        $charactersArray = json_decode($externalApiResponse->body());
        $id = 0;

        // Loop through the array and only return the characters based on the gender passed in 
        // the request
        foreach ($charactersArray as $character) {

                $characterObject = (object) [
                    'id' => ++$id,
                    'name' => $character->name,
                    'gender' => $character->gender,
                    'age' => rand(0, 80), // Age data from external APi isn't suitable
                ];

                $response[] = $characterObject;
            
        }

        // Enable sorting by name in alphabetical order (ascending or descending) and by age then returning
        // the sorted characters as JSON response
        $sortedCharacters = $this->sortCharacters($response, $request->input('sortType'), $request->input('sortOrder'), $request->input('filter'));
        $totalAges = $this->addUpCharacterAges($sortedCharacters);

        return response()->json([
            'characters' => empty($sortedCharacters)
                ? array(
                    'error' => true,
                    'message' => 'An error occurred'
                )
                : $sortedCharacters,
            'characterCount' => count($sortedCharacters), // return characterCount, totalCharacterAge (object containing (age) inMonths and inYears)
            'totalAge' => (object) [
                'inMonths' => $totalAges->inMonths,
                'inYears' => $totalAges->inYears
            ]
        ]);
    }

    /**
     * Sort characters either by name or age in ascending or descending order
     *
     * @param array $characters
     * @param string $sortType
     * @param string $sortOder
     * @return array sortedCharacters
     */
    public function sortCharacters(
        $characters,
        $sortType = '', // Can only be either name (sort by name) or age (sort by age)
        $sortOder = '', // Can only be either in ascending (asc) or descending order (des)
        $filter = '' // Can only either be Male or Female
    ) {

        // Sample objects for testing
        // $characters = array(
        //     (object) [
        //         'id' => 1,
        //         'name' => 'Richard',
        //         'gender' => 'Male',
        //         'age' => 70,
        //     ],
        //     (object) [
        //         'id' => 2,
        //         'name' => 'Zawadi',
        //         'gender' => 'Female',
        //         'age' => 24,
        //     ],
        //     (object) [
        //         'id' => 3,
        //         'name' => 'Ashley',
        //         'gender' => 'Female',
        //         'age' => 20,
        //     ]
        // );

        if ($sortType == 'name') {

            // Loop through the character objects to create a new array containing only id and name
            foreach ($characters as $character) {
                $names[$character->id] = $character->name;
            }

            // Sort in ascending order
            if ($sortOder == 'asc') {
                asort($names);
            }

            // Sort in descending order
            if ($sortOder == 'des') {
                arsort($names);
            }

            // Use the sorted $names to produce a new array with the order of items changed
            foreach ($names as $id => $name) {
                $response[] = $characters[$id - 1];
            }

            if ($filter == 'Male' || $filter == 'Female') {
                return $this->filterCharacters($response, $filter);
            }
            else {
                return $response;
            }

        }
        
        elseif ($sortType == 'age') {

            // Loop through the character objects to create a new array containing only id and age
            foreach ($characters as $character) {
                $ages[$character->id] = $character->age;
            }

            // Sort in ascending order
            if ($sortOder == 'asc') {
                asort($ages);
            }

            // Sort in descending order
            if ($sortOder == 'des') {
                arsort($ages);
            }

            // Use the sorted $ages to produce a new array with the order of items changed
            foreach ($ages as $id => $age) {
                $response[] = $characters[$id - 1];
            }

            if ($filter == 'Male' || $filter == 'Female') {
                return $this->filterCharacters($response, $filter);
            }
            else {
                return $response;
            }
            
        }

        elseif ($filter == 'Male' || $filter == 'Female') {

            return $this->filterCharacters($characters, $filter);
        }

        else {
            return $characters;
        }

    }

    /**
     * Filters characters by gender: Male or Female
     *
     * @param array $characters
     * @param string $filter
     * @return array
     */
    private function filterCharacters($characters, $filter) {

        foreach ($characters as $character) {
            if ($character->gender == $filter) {
                $response[] = $character;
            }
            continue;
        }

        return $response;
    }

    /**
     * Get the sum total of the ages of all the characters.
     *
     * @param array $characters
     * @return object totalAgesObject containing age in months and in years
     */
    private function addUpCharacterAges($characters) {
        $totalAges = 0;

        foreach ($characters as $character) {
            $totalAges = $totalAges + $character->age;
        }

        $totalAgesObject = (object) [
            'inMonths' => $totalAges * 12,
            'inYears' => $totalAges
        ];

        return $totalAgesObject;
    }
}
