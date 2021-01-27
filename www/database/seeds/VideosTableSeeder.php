<?php

use Illuminate\Database\Seeder;
use App\Models\Genero;
use App\Models\Video;

class VideosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $generos = Genero::all();
        factory(\App\Models\Video::class, 100)
        ->create()
        ->each(function(Video $video) use ($generos){
            $subGeneros = $generos->random(5)->load('categories');
            $categoriesId = [];
            foreach ($subGeneros as $genero) {
                array_push($categoriesId, ...$genero->categories->pluck('id')->toArray());
            }
            $categoriesId = array_unique($categoriesId);
            $video->categories()->attach($categoriesId);
            $video->generos()->attach($subGeneros->pluck('id')->toArray());
        });
    }
}
