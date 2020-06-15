<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Impression;
use Faker\Generator as Faker;

$factory->define(Impression::class, function (Faker $faker) {
    $randomTime = $faker->dateTimeThisMonth();

    return [
        'created_at' => $randomTime,
        'updated_at' => $randomTime
    ];
});
