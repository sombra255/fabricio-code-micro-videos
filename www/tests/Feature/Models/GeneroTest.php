<?php

namespace Tests\Feature\Models;

use App\Models\Genero;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class GeneroTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Genero::class, 1)->create();
        $generos = Genero::all();

        $this->assertCount(1, $generos);
        $generoKey = array_keys($generos->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ],
        $generoKey);
    }

    public function testCreate()
    {
        $genero = factory(Genero::class, 1)->create()->first();
        $genero->refresh();
        $this->assertNotNull($genero->name);
        $this->assertTrue($genero->is_active);
        $this->assertTrue(Uuid::isValid($genero->id)); //check UUID is valid
    }

    public function testCreate_isActiveFalse()
    {
        $genero = factory(Genero::class, 1)->create([
            'is_active' => false
        ])->first();
        $this->assertFalse($genero->is_active);
    }

    public function testUpdate()
    {
        $genero = factory(Genero::class)->create([
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'update_name',
            'is_active' => true
        ];
        $genero->update($data);
        foreach($data as $key => $value){
            $this->assertEquals($value, $genero->{$key});
        }
    }

    public function testDelete()
    {
        factory(Genero::class, 5)->create()->first();
        $generos = Genero::all();
        $this->assertCount(5, $generos);
        for ($i=0; $i < count($generos); $i=$i+2) { 
            $generos[$i]->delete();
        }
        $generos = Genero::all();
        $this->assertCount(2, $generos);
        
    }
}
