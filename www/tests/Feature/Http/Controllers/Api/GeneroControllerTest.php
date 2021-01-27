<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GeneroController;
use App\Models\Category;
use App\Models\Genero;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
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
            'name' => '',
            'categories_id' => ''
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

        $data = [
            'categories_id' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'array');

        $data = [
            'categories_id' => [100]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            'categories_id' => [$category->id]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');

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
            'name' => '',
            'categories_id' => ''
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

        $data = [
            'categories_id' => 'a'
        ];
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'categories_id' => [100]
        ];
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            'categories_id' => [$category->id]
        ];
        $this->assertInvalidationInUpdateAction($data, 'exists');

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
        $categoryId = factory(Category::class)->create()->id;
        $data = [
            'name' => 'test'
        ];
        $response = $this->assertStore(
            $data + ['categories_id' => [$categoryId]], 
            $data + ['name' => 'test', 'is_active' => true, 'deleted_at' => null]
        );
            
        // $response = $this->json('POST', route('generos.store'), [
        //     'name' => 'test'
        // ]);
        
        // $id = $response->json('id');  
        // $genero = Genero::find($id); 
        
        // $response
        //     ->assertStatus(201)
        //     ->assertJson($genero->toArray());
        // $this->assertTrue($response->json('is_active'));

        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);
        $this->assertHasCategory($response->json('id'), $categoryId);

        $data = [
            'name' => 'test',
            'is_active' => false
        ];
        $response = $this->assertStore(
            $data + ['categories_id' => [$categoryId]], 
            $data + ['name' => 'test', 'is_active' => false, 'deleted_at' => null]);

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

        $categoryId = factory(Category::class)->create()->id;
        $data = [
            'name' => 'test',
            'is_active' => true
        ];

        $response = $this->assertUpdate(
            $data + ['categories_id' => [$categoryId]], 
            $data + ['deleted_at' => null]);

        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);
        $this->assertHasCategory($response->json('id'), $categoryId);

        $data['name'] = 'test';
        $this->assertUpdate(
            $data + ['categories_id' => [$categoryId]],
            array_merge($data, ['name' => 'test']));

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

    protected function assertHasCategory($generoId, $categoryId)            
    {
        $this->assertDatabaseHas('category_genero', [
            'genero_id' => $generoId,
            'category_id' => $categoryId,
        ]);
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();

        $sendData = [
            'name' => 'test',
            'categories_id' => [$categoriesId[0]]
        ];
        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertDatabaseHas('category_genero', [
            'category_id' => $categoriesId[0],
            'genero_id' => $response->json('id')
        ]);

        $sendData = [
            'name' => 'test',
            'categories_id' => [$categoriesId[1], $categoriesId[2]]
        ];
        $response = $this->json(
            'PUT',
            route('generos.update', ['genero' => $response->json('id')]),
            $sendData
        );

        $this->assertDatabaseMissing('category_genero', [
            'category_id' => $categoriesId[0],
            'genero_id' => $response->json('id')
        ]);
        $this->assertDatabaseHas('category_genero', [
            'category_id' => $categoriesId[1],
            'genero_id' => $response->json('id')
        ]);
        $this->assertDatabaseHas('category_genero', [
            'category_id' => $categoriesId[2],
            'genero_id' => $response->json('id')
        ]);

    }

    public function testRollbackStore()
    {
        $controller = \Mockery::mock(GeneroController::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                'name' => 'test'
            ]);

        $controller
            ->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);

        $controller->shouldReceive('handleRelations')
        ->once()
        ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);

        $hasError = false;
        try {
            $controller->store($request);
        } catch (\Tests\Exceptions\TestException $exception) {
            $this->assertCount(1, Genero::all());
            $hasError = true;
        }
        
        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(GeneroController::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->genero);

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                'name' => 'test'
            ]);

        $controller
            ->shouldReceive('rulesUpdate')
            ->withAnyArgs()
            ->andReturn([]);

        $controller->shouldReceive('handleRelations')
        ->once()
        ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);
        
        $hasError = false;
        try {
            $controller->update($request, 1);
        } catch (\Tests\Exceptions\TestException $exception) {
            $this->assertCount(1, Genero::all());
            $hasError = true;
        }
        
        $this->assertTrue($hasError);

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
