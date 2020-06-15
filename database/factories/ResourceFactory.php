<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Resource;
use Faker\Generator as Faker;
use App\utils\Swapi;

$factory->define(Resource::class, function (Faker $faker) {
    return [
        'swapi_id' => '',
        'category' => '',
        'name' => $faker->name()
    ];
});
