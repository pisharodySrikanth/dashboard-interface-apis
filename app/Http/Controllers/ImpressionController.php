<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Resource;
use App\Impression;

class ImpressionController extends Controller
{
    public function index(Request $request) {
        $params = $request->all();

        $rules = [
            'start' => ['date_format:Y-m-d','before:end', 'required'],
            'end' => ['date_format:Y-m-d', 'after:start'],
            'dimensions' => ['array', 'required']
        ];

        //adding rules for filters
        foreach($params as $key=>$val) {
            if(!array_key_exists($key, $rules)) {
                $rules[$key] = ['json', 'string'];
            }
        }
    
        $validator = Validator::make($params, $rules);

        if($validator->fails()) {
            return [
                'success' => false,
                'msg' => 'filters are not proper'
            ];
        }

        return [
            'success' => true,
            'data' => Impression::getList($request)
        ];
    }

    public function store(Request $request, $categoryName) {
        //validate all inputs
        $validatedFields = $request->validate([
            'resourceName' => ['required', 'max:255', 'string'],
            'swapiId' => ['required', 'int']
        ]);

        $resourceName = $validatedFields['resourceName'];
        $swapiId = $validatedFields['swapiId'];
        
        try {
            //begin transaction
            DB::beginTransaction();

            //if not exists create resource
            $resource = Resource::firstOrCreate([
                'category' => $categoryName,
                'swapi_id' => $swapiId
            ], [
                'swapi_id' => $swapiId,
                'name' => $resourceName,
                'category' => $categoryName
            ]);
            
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
