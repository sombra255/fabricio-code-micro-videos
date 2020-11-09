<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;
    
    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));

        $response
        ->assertStatus(200)
        ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));

        $response
        ->assertStatus(200)
        ->assertJson($category->toArray());
    }

    public function testInvalidationDataAttributeName()
    {
        $response = $this->json('POST', route('categories.store'), []);
        $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name'])
        ->assertJsonFragment([
            \Lang::get('validation.required', ['attribute' => 'name'])
        ]);
    }

    public function testMaxCaracter()
    {
        $response = $this->json('POST', route('categories.store', []), [
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
        $response = $this->json('POST', route('categories.store'), []);
        $response
        ->assertStatus(422)
        ->assertJsonMissingValidationErrors(['is_active'])
        ->assertJsonFragment([
            \Lang::get('validation.required', ['attribute' => 'name'])
        ]);
    }

    public function testInvalidValueInIsActive()
    {
        $response = $this->json('POST', route('categories.store', []), [
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
        $category = factory(Category::class)->create();
        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), []);

        $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name'])
        ->assertJsonMissingValidationErrors(['is_active'])
        ->assertJsonFragment([
            \Lang::get('validation.required', ['attribute' => 'name'])
        ]);


        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), 
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
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test'
        ]);
        
        $id = $response->json('id');  
        $category = Category::find($id); 
        
        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test',
            'is_active' => false,
            'description' => 'description'
        ]);
        
        $response
            ->assertJsonFragment([
                'is_active' => false,
                'description' => 'description'
            ]);
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false
        ]);
        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => 'test',
            'is_active' => true,
            'description' => 'test'
        ]);
        
        $id = $response->json('id');  
        $category = Category::find($id); 
        
        $response
            ->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'description' => 'test',
                'is_active' => true
        ]);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => 'test',
            'description' => ''
        ]);

        $response
            ->assertJsonFragment([
                'description' => null     
        ]);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => 'test',
            'description' => null
        ]);

        $category->description = 'test';
        $category->save();

        $response
            ->assertJsonFragment([
                'description' => null     
        ]);
    }

    public function testDelete()
    {
        $category = factory(Category::class)->create();
        $response = $this->delete(route('categories.destroy', ['category' => $category->id]));
        
          
        $categories = Category::all(); 

        $response
            ->assertStatus(204)
            ->assertNoContent();
        $this->assertCount(0, $categories);
    }
}
