<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\utils\DateFormat;

class Resource extends Model
{
    public $guarded = [];

    private static function getImpressionQuery($query, $params) {
        $format = DateFormat::getFormat($params['granularity']);
        $dateStr = 'DATE_FORMAT(impressions.created_at, "'.$format.'")';

        //adding filters
        $query
        ->where('impressions.created_at', '>=', $params['start']);

        if(!empty($params['end'])) {
            $query->where('impressions.created_at', '<=', $params['end']);
        }

        $query
            ->groupBy(DB::raw($dateStr), 'resource_id')
            ->addSelect('resource_id')
            ->addSelect(DB::raw("{$dateStr} as date"), DB::raw('COUNT(1) as impressions'));

    }

    public static function getList($request, $categoryName) {
        $queryParams = $request->all();


        $resources = static::where('category', $categoryName)
            ->addSelect('name', 'id')
            ->with(['impressions' => function($query) use(&$queryParams) {
                static::getImpressionQuery($query, $queryParams);
            }])
            ->get();

        //returning resources with impressions > 0
        return $resources->filter(function($r) {
            return count($r['impressions']) > 0;
        });
    }

    public function impressions() {
        return $this->hasMany('App\Impression');
    }
}
