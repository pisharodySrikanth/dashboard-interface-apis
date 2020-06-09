<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Impression extends Model
{
    public $guarded = [];

    private static function addDateParams($query, $dimensions, $request) {
        $dateFormatMap = [
            'hour' => '%d-%m-%Y %h:00:00',
            'day' => '%d-%m-%Y'
        ];

        if(!array_key_exists('date', $dimensions) || !array_key_exists($dimensions['date'], $dateFormatMap)) {
            return;
        }

        //adding date dimension
        $dateFormatStr = "DATE_FORMAT(impressions.created_at, \"{$dateFormatMap[$dimensions['date']]}\")";

        $query
            ->groupBy(DB::raw($dateFormatStr))
            ->addSelect(DB::raw("{$dateFormatStr} as date"));
            
        //add date filter
        if($request['start'] && $request['end']) {
            $query->whereBetween('impressions.created_at', [$request['start'], $request['end']]);
        }
    }

    private static function addCategoryParams($query, $dimensions, $request) {
        if(!array_key_exists('category', $dimensions)) {
            return;
        }

        //adding category dimension
        $query
            ->where('resources.category', $dimensions['category'])
            ->groupBy('resources.id', 'resources.swapi_id')
            ->addSelect('resources.name as resource', 'resources.swapi_id');

        //add category filter
        if($request['resources']) {
            $query->whereIn('resources.swapi_id', $request['resources']);
        }
    }
    
    public static function getList($request) {
        $dimensions = json_decode($request['dimensions'], true);
        $query = static::join('resources', 'impressions.resource_id', '=', 'resources.id');

        static::addDateParams($query, $dimensions, $request);
        static::addCategoryParams($query, $dimensions, $request);

        return $query
            ->addSelect(DB::raw('count(1) as impressions'))
            ->get();
    }

    public function resource() {
        return $this->belongsTo('App\Resource');
    }
}
