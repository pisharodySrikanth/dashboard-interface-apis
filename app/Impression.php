<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\utils\Swapi;

class Impression extends Model
{
    public $guarded = [];

    private static $dateFormatMap = [
        'hour' => '%d-%m-%Y %h:00:00',
        'day' => '%d-%m-%Y'
    ];

    private static function getDateFormatStr($dateType) {
        if(!array_key_exists($dateType, static::$dateFormatMap)) {
            return null;
        }

        return 'DATE_FORMAT(impressions.created_at, "'.static::$dateFormatMap[$dateType].'")';
    }

    public static function addDimensions($query, $dimensions) {
        $dateType = $dimensions['dateType'];
        $otherDimensions = $dimensions['dimensions'];
        
        if(!empty($dateType)) {
            $dateFormat = static::getDateFormatStr($dateType);
            if($dateFormat) {
                $query
                    ->groupBy(DB::raw($dateFormat))
                    ->addSelect(DB::raw("{$dateFormat} as date"));
            }
        }

        if(!empty($otherDimensions)) {
            $query
                ->addSelect("resources.name as {$otherDimensions[0]}")
                ->groupBy('resources.category', 'resources.swapi_id', 'resources.name')
                ->where('resources.category', $otherDimensions[0]);
        }
    }

    private static function addFilter($query, $filters, $dimensions) {
        $start = '';
        $end = '';
        $otherFilters = [];

        //separating date and categories
        foreach($filters as $key => $val) {
            switch($key) {
                case 'start':
                    $start = $val;
                break;
                case 'end':
                    $end = $val;
                break;
                default:
                    $otherFilters[$key] = $val;
                break;
            }
        }

        $query->where('impressions.created_at', '>=', $start);
        
        if(!empty($end)) {
            $query->where('impressions.created_at', '<=', $end);
        }

        if(!empty($otherFilters)) {
            $swapiIds = Swapi::getQualifiedIds($otherFilters, $dimensions['dimensions']);

            $query->where(function($query) use (&$swapiIds) {
                foreach($swapiIds  as $key => $val) {
                    $query->orWhere(function($query) use(&$key, &$val) {
                        $query
                            ->where('resources.category', $key)
                            ->whereIn('resources.swapi_id', $val);
                    });
                }
            });
        }
    }

    public static function segregateDimensions($dimensions) {
        $dateType = '';
        $otherDimensions = [];

        //separating date from other dimensions
        foreach($dimensions as $val) {
            if(array_key_exists($val, static::$dateFormatMap)) {
                if(empty($dateType)) {
                    $dateType = $val;
                }
            } else {
                $otherDimensions[] = $val;
            }
        }

        return [
            'dateType' => $dateType,
            'dimensions' => $otherDimensions
        ];
    }
    
    public static function getList($request) {
        $query = static::join('resources', 'impressions.resource_id', '=', 'resources.id');

        $queryParams = $request->all();

        $dimensions = $queryParams['dimensions'];
        $segregatedDimensions = static::segregateDimensions($dimensions);

        //removing dimensions so that only filters remain
        unset($queryParams['dimensions']);

        static::addDimensions($query, $segregatedDimensions);
        static::addFilter($query, $queryParams, $segregatedDimensions);

        return $query
            ->addSelect(DB::raw('count(1) as impressions'))
            ->orderBy('impressions', 'desc')
            ->get();
    }

    public function resource() {
        return $this->belongsTo('App\Resource');
    }
}
