<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genero;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Traits\TestValidations, Tests\Traits\TestSaves;

class GeneroControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $genero;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genero = factory(Genero::class)->create();
    }

    public function testIndex()
    {
        // $genero = factory(Genero::class)->create();
        $response = $this->get(route('generos.index'));

        $response
        ->assertStatus(200)
        ->assertJson([$this->genero->toArray()]);
    }

    public function testShow()
    {
        // $genero = factory(Genero::class)->create();
        $response = $this->get(route('generos.show', ['genero' => $this->genero->id]));

        $response
        ->assertStatus(200)
        ->assertJson($this->genero->toArray());
    }

    public function testInvalidationDataAttributeName()
    {
        $data = [
            'name' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        // $response = $this->json('POST', route('generos.store'), []);
        // $response
        // ->assertStatus(422)
        // ->assertJsonValidationErrors(['name'])
        // ->assertJsonFragment([
        //     \Lang::get('validation.required', ['attribute' => 'name'])
        // ]);
    }

    public function testMaxCaracter()
    {
        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        // $response = $this->json('POST', route('generos.store', []), [
        //     'name' => str_repeat('a', 256)
        // ]);
        // $response
        // ->assertStatus(422)
        // ->assertJsonValidationErrors(['name'])
        // ->assertJsonFragment([
        //     \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
        // ]);
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
        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        // $response = $this->json('POST', route('generos.store', []), [
        //     'is_active' => 'a'
        // ]);
        // $response
        // ->assertStatus(422)
        // ->assertJsonValidationErrors(['name'])
        // ->assertJsonFragment([
        //     \Lang::get('validation.boolean', ['attribute' => 'is active'])
        // ]);
    }

    public function testUpdateInvalidationData()
    {
        // $genero = factory(Genero::class)->create();
        $data = [
            'name' => ''
        ];
        $this->assertInvalidationInUpdateAction($data, 'required');
        // $response = $this->json('PUT', route('generos.update', ['genero' => $this->genero->id]), []);

        // $response
        // ->assertStatus(422)
        // ->assertJsonValidationErrors(['name'])
        // ->assertJsonMissingValidationErrors(['is_active'])
        // ->assertJsonFragment([
        //     \Lang::get('validation.required', ['attribute' => 'name'])
        // ]);



        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInUpdateAction($data, 'boolean');
        // $response = $this->json('PUT', route('generos.update', ['genero' => $this->genero->id]), 
        // [
        //     'name' => str_repeat('a', 256),
        //     'is_active' => 'a'
        // ]);
        // $response
        // ->assertStatus(422)
        // ->assertJsonValidationErrors(['name', 'is_active'])
        // ->assertJsonFragment([
        //     \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
        // ])
        // ->assertJsonFragment([
        //     \Lang::get('validation.boolean', ['attribute' => 'is active'])
        // ]);
    }

    public function testStore()
    {
        $data = [
            'name' => 'test'
        ];
        $this->assertStore($data, $data + ['name' => 'test', 'is_active' => true, 'deleted_at' => null]);
        // $response = $this->json('POST', route('generos.store'), [
        //     'name' => 'test'
        // ]);
        
        // $id = $response->json('id');  
        // $genero = Genero::find($id); 
        
        // $response
        //     ->assertStatus(201)
        //     ->assertJson($genero->toArray());
        // $this->assertTrue($response->json('is_active'));

        $data = [
            'name' => 'test',
            'is_active' => false
        ];
        $response = $this->assertStore($data, $data + ['name' => 'test', 'is_active' => false, 'deleted_at' => null]);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        // $response = $this->json('POST', route('generos.store'), [
        //     'name' => 'test',
        //     'is_active' => false         
        // ]);
        
        // $response
        //     ->assertJsonFragment([
        //         'is_active' => false
        //     ]);
    }

    public function testUpdate()
    {
        $this->genero = factory(Genero::class)->create([
            'is_active' => false
        ]);

        $data = [
            'name' => 'test',
            'is_active' => true
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        $data['name'] = 'test';
        $this->assertUpdate($data, array_merge($data, ['name' => 'test']));

        // $response = $this->json('PUT', route('generos.update', ['genero' => $genero->id]), [
        //     'name' => 'test',
        //     'is_active' => true
        // ]);
        
        // $id = $response->json('id');  
        // $genero = Genero::find($id); 
        
        // $response
        //     ->assertStatus(200)
        //     ->assertJson($genero->toArray())
        //     ->assertJsonFragment([
        //         'is_active' => true
        // ]);

        // $response = $this->json('PUT', route('generos.update', ['genero' => $genero->id]), [
        //     'name' => 'test'
        // ]);

        // $response
        //     ->assertJsonFragment([
        //         'name' => 'test'     
        // ]);
    }

    public function testDelete()
    {
        // $genero = factory(Genero::class)->create();
        $response = $this->delete(route('generos.destroy', ['genero' => $this->genero->id]));
        
          
        $generos = Genero::all(); 

        $response
            ->assertStatus(204)
            ->assertNoContent();
        $this->assertCount(0, $generos);
        $this->assertNull(Genero::find($this->genero->id));
        $this->assertNotNull(Genero::withTrashed()->find($this->genero->id));
    }

    protected function routeStore()
    {
        return route('generos.store');
    }

    protected function routeUpdate()
    {
        return route('generos.update', ['genero' => $this->genero->id]);
    }

    protected function model()
    {
        return Genero::class;
    }
    
}
