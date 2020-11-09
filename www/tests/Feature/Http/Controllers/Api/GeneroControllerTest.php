<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genero;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class GeneroControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $genero = factory(Genero::class)->create();
        $response = $this->get(route('generos.index'));

        $response
        ->assertStatus(200)
        ->assertJson([$genero->toArray()]);
    }

    public function testShow()
    {
        $genero = factory(Genero::class)->create();
        $response = $this->get(route('generos.show', ['genero' => $genero->id]));

        $response
        ->assertStatus(200)
        ->assertJson($genero->toArray());
    }

    public function testInvalidationDataAttributeName()
    {
        $response = $this->json('POST', route('generos.store'), []);
        $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name'])
        ->assertJsonFragment([
            \Lang::get('validation.required', ['attribute' => 'name'])
        ]);
    }

    public function testMaxCaracter()
    {
        $response = $this->json('POST', route('generos.store', []), [
            'name' => str_repeat('a', 256)
        ]);
        $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name'])
        ->assertJsonFragment([
            \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
        ]);
    }

    public function testInvalidationDataWithoutIsActive()
    {
        $response = $this->json('POST', route('generos.store'), []);
        $response
        ->assertStatus(422)
        ->assertJsonMissingValidationErrors(['is_active'])
        ->assertJsonFragment([
            \Lang::get('validation.required', ['attribute' => 'name'])
        ]);
    }

    public function testInvalidValueInIsActive()
    {
        $response = $this->json('POST', route('generos.store', []), [
            'is_active' => 'a'
        ]);
        $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name'])
        ->assertJsonFragment([
            \Lang::get('validation.boolean', ['attribute' => 'is active'])
        ]);
    }

    public function testUpdateInvalidationData()
    {
        $genero = factory(Genero::class)->create();
        $response = $this->json('PUT', route('generos.update', ['genero' => $genero->id]), []);

        $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name'])
        ->assertJsonMissingValidationErrors(['is_active'])
        ->assertJsonFragment([
            \Lang::get('validation.required', ['attribute' => 'name'])
        ]);


        $response = $this->json('PUT', route('generos.update', ['genero' => $genero->id]), 
        [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'is_active'])
        ->assertJsonFragment([
            \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
        ])
        ->assertJsonFragment([
            \Lang::get('validation.boolean', ['attribute' => 'is active'])
        ]);
    }

    public function testStore()
    {
        $response = $this->json('POST', route('generos.store'), [
            'name' => 'test'
        ]);
        
        $id = $response->json('id');  
        $genero = Genero::find($id); 
        
        $response
            ->assertStatus(201)
            ->assertJson($genero->toArray());
        $this->assertTrue($response->json('is_active'));

        $response = $this->json('POST', route('generos.store'), [
            'name' => 'test',
            'is_active' => false         
        ]);
        
        $response
            ->assertJsonFragment([
                'is_active' => false
            ]);
    }

    public function testUpdate()
    {
        $genero = factory(Genero::class)->create([
            'is_active' => false
        ]);
        $response = $this->json('PUT', route('generos.update', ['genero' => $genero->id]), [
            'name' => 'test',
            'is_active' => true
        ]);
        
        $id = $response->json('id');  
        $genero = Genero::find($id); 
        
        $response
            ->assertStatus(200)
            ->assertJson($genero->toArray())
            ->assertJsonFragment([
                'is_active' => true
        ]);

        $response = $this->json('PUT', route('generos.update', ['genero' => $genero->id]), [
            'name' => 'test'
        ]);

        $response
            ->assertJsonFragment([
                'name' => 'test'     
        ]);
    }

    public function testDelete()
    {
        $genero = factory(Genero::class)->create();
        $response = $this->delete(route('generos.destroy', ['genero' => $genero->id]));
        
          
        $generos = Genero::all(); 

        $response
            ->assertStatus(204)
            ->assertNoContent();
        $this->assertCount(0, $generos);
    }
    
}
