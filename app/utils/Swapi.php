<?php

namespace App\utils;

class Swapi {
    private static $relations = [
        'people' => ['species', 'vehicles']
    ];

    public static function fetch($resource, $id=null) {
        $client = new \GuzzleHttp\Client();

        $url = "https://swapi.dev/api/{$resource}/";
        if(!empty($id)) {
            $url .= $id;
        }

        $res = $client->request('GET', $url);

        $statusCode = $res->getStatusCode();

        if($statusCode !== 200) {
            return null;
        }

        $response = \json_decode((string)$res->getBody(), true);

        return $response['results'];
    }

    public static function getIdFromUrl($dimension, $url) {
        $id = '';
        $matches = [];
        $regex = "/http(s)?:\/\/swapi.dev\/api\/{$dimension}\/(?P<digit>\d+)/";
        
        preg_match($regex, $url, $matches);

        return $matches['digit'];
    }

    private static function getFilteredIds($dimension, $results, $filters) {
        $dimensionData = $results[$dimension];
        
        $filtered = array_filter($dimensionData, function($item) use (&$dimension, &$filters) {
            $shouldAdd = true;
            
            foreach($filters as $key => $val) {
                $decoded = json_decode($val, true);
                $type = $decoded['type'];
                $filterArray = $decoded['value'];

                if($key === $dimension) {
                    $id = static::getIdFromUrl($dimension, $item['url']);
                    $shouldAdd = $shouldAdd && in_array($id, $filterArray);
                } else if(key_exists($key, $item)) {
                    //see if child
                    $filteredChildren = array_filter($item[$key], function($i) use (&$filterArray, &$key) {
                        return in_array(static::getIdFromUrl($key, $i), $filterArray);
                    });

                    $shouldAdd = $shouldAdd && !empty($filteredChildren);
                } else {
                    $shouldAdd = false;
                }

                if(!$shouldAdd) {
                    break;
                }
            }

            return $shouldAdd;
        });

        //extracting only ids from the filtered array
        return array_map(function($item) use(&$dimension) {
            return static::getIdFromUrl($dimension, $item['url']);
        }, $filtered);
    }

    public static function getQualifiedIds($filters, $dimensions) {
        $categories = [];
        $result = [];

        //if no dimensions, filter keys will go in where clause
        if(empty($dimensions)) {
            foreach($filters as $key => $val) {
                $decoded = json_decode($val, true);
                $values = array_key_exists('value', $decoded) ? $decoded['value'] : [];
                $result[$key] = $values;
            }

            return $result;
        }

        //fetch all dimension categories
        foreach($dimensions as $key) {
            $response = static::fetch($key);
            $categories[$key] = empty($response) ? [] : $response;
        }

        //find qualified ids for each dimensions based on filters
        foreach($dimensions as $d) {
            $result[$d] = static::getFilteredIds($d, $categories, $filters);
        }

        // get qualified ids for each dimension
        return $result;
    }
}

