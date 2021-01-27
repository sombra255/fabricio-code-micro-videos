<?php

use App\Models\Category;
use App\Models\Genero;
use Illuminate\Database\Seeder;

class GenerosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = Category::all();
        factory(\App\Models\Genero::class, 100)
            ->create()
            ->each(function(Genero $genero) use($categories){
                $categoriesId = $categories->random(5)->pluck('id')->toArray();
                $genero->categories()->attach($categoriesId);
            });
    }
}
