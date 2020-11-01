<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testList()
    {
        factory(Category::class, 1)->create();
        $categories = Category::all();

        $this->assertCount(1, $categories);
        $categoryKey = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ],
        $categoryKey);
    }
    
    public function testCreate()
    {
        $category = Category::create([
            'name' => 'test1'
        ]);
        $category->refresh();
        $this->assertEquals('test1', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);
        $this->assertTrue(Uuid::isValid($category->id)); //check UUID is valid
    }

    public function testCreate_descriptionNotNull()
    {
        $category = Category::create([
            'name' => 'test1',
            'description' => 'test_description'
        ]);
        $this->assertEquals('test_description', $category->description);
    }

    public function testCreate_descriptionIsNull()
    {
        $category = Category::create([
            'name' => 'test1',
            'description' => null
        ]);
        $this->assertNull($category->description);
    }

    public function testCreate_isActive()
    {
        $category = Category::create([
            'name' => 'test1',
            'is_active' => false
        ]);
        $this->assertFalse($category->is_active);

        $category = Category::create([
            'name' => 'test1',
            'is_active' => true
        ]);
        $this->assertTrue($category->is_active);
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'description' => 'test_description',
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'update_name',
            'description' => 'update_description',
            'is_active' => true
        ];
        $category->update($data);
        foreach($data as $key => $value){
            $this->assertEquals($value, $category->{$key});
        }
        $this->assertEquals('update_name', $category->name);
        $this->assertEquals('update_description', $category->description);
        $this->assertTrue($category->is_active);
    }

    public function testDelete()
    {
        $category = factory(Category::class, 5)->create([
            'description' => 'test_description'
        ])->first();
        $categories = Category::all();
        $this->assertCount(5, $categories);
        $category->delete();
        $categories = Category::all();
        $this->assertCount(4, $categories);
        
    }
}
