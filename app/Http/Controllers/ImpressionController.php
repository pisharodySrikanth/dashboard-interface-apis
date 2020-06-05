<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Category;
use App\Resource;
use App\Impression;

class ImpressionController extends Controller
{
    public function index() {
        return 'returns the list';
    }

    public function store(Request $request, $categoryName, $swapiId) {
        //validate all inputs
        $validatedFields = $request->validate([
            'resourceName' => ['required', 'max:255']
        ]);

        $resourceName = $validatedFields['resourceName'];
        
        try {
            //begin transaction
            DB::beginTransaction();

            //if not exists create category
            $category = Category::firstOrCreate([
                'name' => $categoryName
            ]);

            if($category->wasRecentlyCreated) {
                $resource = Resource::create([
                    'swapi_id' => $swapiId,
                    'name' => $resourceName,
                    'category_id' => $category->id
                    ]);
            } else {
                //if not exists create resource
                $resource = Resource::firstOrCreate([
                    'category_id' => $category->id,
                    'swapi_id' => $swapiId
                ], [
                    'swapi_id' => $swapiId,
                    'name' => $resourceName,
                    'category_id' => $category->id
                ]);
            }
            
            //create impression
            $impression = Impression::create([
                'resource_id' => $resource->id
            ]);

            //commit transaction
            DB::commit();

            return [
                'success' => true
            ];
        } catch(Exception $e) {
            DB::rollBack();
            return [
                'success' => false
            ];
        }
    }
}
