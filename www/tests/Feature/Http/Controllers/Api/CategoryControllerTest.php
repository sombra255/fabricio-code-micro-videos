<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase, Tests\Traits\TestValidations, Tests\Traits\TestSaves;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;
    
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
    }
    
    public function testIndex()
    {
        // $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));

        $response
        ->assertStatus(200)
        ->assertJson([$this->category->toArray()]);
    }

    public function testShow()
    {
        // $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));

        $response
        ->assertStatus(200)
        ->assertJson($this->category->toArray());
    }

    public function testInvalidationDataAttributeName()
    {
        $data = [
            'name' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        // $response = $this->json('POST', route('categories.store'), []);
        // $this->assertInvalidationFields(
        //     $response, ['name'], 'required'
        // );
    }

    public function testMaxCaracter()
    {
        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        // $response = $this->json('POST', route('categories.store', []), [
        //     'name' => str_repeat('a', 256)
        // ]);
        // $this->assertInvalidationFields($response, ['name'], 'max.string', ['max' => 255]);
    }

    public function testInvalidationDataWithoutIsActive()
    {
        $response = $this->json('POST', route('categories.store'), []);
        $this->assertInvalidationFields($response, ['name'], 'required');

        $response
        ->assertJsonMissingValidationErrors(['is_active']);
    }

    public function testInvalidValueInIsActive()
    {
        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        // $response = $this->json('POST', route('categories.store', []), [
        //     'is_active' => 'a'
        // ]);
        // $this->assertInvalidationFields($response, ['is_active'], 'boolean');


        // $response
        // ->assertStatus(422)
        // ->assertJsonValidationErrors(['name'])
        // ->assertJsonFragment([
        //     \Lang::get('validation.boolean', ['attribute' => 'is active'])
        // ]);
    }

    public function testUpdateInvalidationData()
    {
        // $category = factory(Category::class)->create();
        $data = [
            'name' => ''
        ];
        $this->assertInvalidationInUpdateAction($data, 'required');
        // $response = $this->json('PUT', route('categories.update', ['category' => $this->category->id]), []);
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
        // $response = $this->json('PUT', route('categories.update', ['category' => $this->category->id]), 
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
        $this->assertStore($data, $data + ['name' => 'test', 'description' => null, 'is_active' => true, 'deleted_at' => null]);
        // $response = $this->json('POST', route('categories.store'), [
        //     'name' => 'test'
        // ]);
        
        // $id = $response->json('id');  
        // $category = Category::find($id); 
        
        // $response
        //     ->assertStatus(201)
        //     ->assertJson($category->toArray());
        // $this->assertTrue($response->json('is_active'));
        // $this->assertNull($response->json('description'));

        $data = [
            'name' => 'test',
            'is_active' => false,
            'description' => 'description'
        ];
        $response = $this->assertStore($data, $data + ['name' => 'test', 'description' => 'description', 'is_active' => false]);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        // $response = $this->json('POST', route('categories.store'), [
        //     'name' => 'test',
        //     'is_active' => false,
        //     'description' => 'description'
        // ]);
        
        // $response
        //     ->assertJsonFragment([
        //         'is_active' => false,
        //         'description' => 'description'
        //     ]);
    }

    public function testUpdate()
    {
        $this->category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false
        ]);
        $data = [
            'name' => 'test',
            'is_active' => true,
            'description' => 'test'
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        $data = [
            'name' => 'test',
            'description' => ''
        ];
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data['description'] = 'test';
        $this->assertUpdate($data, array_merge($data, ['description' => 'test']));

        $data['description'] = null;
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        // $response = $this->json('PUT', route('categories.update', ['category' => $this->category->id]), [
        //     'name' => 'test',
        //     'is_active' => true,
        //     'description' => 'test'
        // ]);
        
        // $id = $response->json('id');  
        // $category = Category::find($id); 
        
        // $response
        //     ->assertStatus(200)
        //     ->assertJson($category->toArray())
        //     ->assertJsonFragment([
        //         'description' => 'test',
        //         'is_active' => true
        // ]);

        // $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
        //     'name' => 'test',
        //     'description' => ''
        // ]);

        // $response
        //     ->assertJsonFragment([
        //         'description' => null     
        // ]);

        // $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
        //     'name' => 'test',
        //     'description' => null
        // ]);

        // $category->description = 'test';
        // $category->save();

        // $response
        //     ->assertJsonFragment([
        //         'description' => null     
        // ]);
    }

    public function testDelete()
    {
        // $category = factory(Category::class)->create();
        $response = $this->delete(route('categories.destroy', ['category' => $this->category->id]));
        
          
        $categories = Category::all(); 

        $response
            ->assertStatus(204)
            ->assertNoContent();
        $this->assertCount(0, $categories);
        $this->assertNull(Category::find($this->category->id));
        $this->assertNotNull(Category::withTrashed()->find($this->category->id));
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function model()
    {
        return Category::class;
    }
}
