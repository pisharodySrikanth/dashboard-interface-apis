<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Resource;
use App\Impression;
use App\utils\DateFormat;

class ResourceController extends Controller
{
    public function index(Request $request, $categoryName) {
        $granularityKeys = join(',', DateFormat::getFormatKeys());

        $validator = Validator::make($request->all(), [
            'start' => ['date_format:Y-m-d','before:end', 'required'],
            'end' => ['date_format:Y-m-d', 'after:start'],
            'granularity' => ['string', 'required', "in:{$granularityKeys}"]
        ]);

        if($validator->fails()) {
            return [
                'success' => false,
                'msg' => 'params are not proper'
            ];
        }

        return [
            'success' => true,
            'data' => Resource::getList($request, $categoryName)
        ];
    }
}
