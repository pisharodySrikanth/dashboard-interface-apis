<?php

use Illuminate\Database\Seeder;
use App\utils\Swapi;
use App\Impression;

class ResourceSeeder extends Seeder
{
    private static $categories = ['films', 'starships', 'species', 'vehicles', 'planets', 'people'];

    private static $resourcePerCat = 5;

    private static $impressionPerResource = 100;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        foreach(static::$categories as $cat) {
            $resourceList = Swapi::fetch($cat);
            $resources = factory(App\Resource::class, static::$resourcePerCat)
                ->make()
                ->each(function($r) use(&$cat, &$faker, &$resourceList, &$impressionPerResource) {
                    $random = $faker->randomElement($resourceList);
                    $r['name'] = array_key_exists('title', $random) ? $random['title'] : $random['name'];
                    $r['swapi_id'] = Swapi::getIdFromUrl($cat, $random['url']);
                    $r['category'] = $cat;
                    $r->save();

                    $r->impressions()->createMany(
                        factory(App\Impression::class, static::$impressionPerResource)
                            ->make()->toArray()
                    );
                });
        }
    }
}
