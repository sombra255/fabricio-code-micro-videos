<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\GeneroResource;
use App\Http\Controllers\Api\GeneroController;
use App\Models\Category;
use App\Models\Genero;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\Traits\TestValidations, Tests\Traits\TestSaves, Tests\Traits\TestResources;

class GeneroControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;

    private $genero;
    private $serializedFields = [
        'id',
        'name',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
        'categories' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ]
        ]
    ];

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
        ->assertJsonStructure([
            'data' => [
                '*' => $this->serializedFields
            ],
            'links' => [],
            'meta' => []
        ]);
        $resource = GeneroResource::collection(collect([$this->genero]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        // $genero = factory(Genero::class)->create();
        $response = $this->get(route('generos.show', ['genero' => $this->genero->id]));

        $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => $this->serializedFields
        ])
        ->assertJsonFragment($this->genero->toArray());

        $resource = new GeneroResource($this->genero);
        $this->assertResource($response, $resource);
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

    public function testSave()
    {
        $categoryId = factory(Category::class)->create()->id;
        $data = 
        [
            [
                'send_data' => [
                    'name' => 'test',
                    'categories_id' => [$categoryId]
                ],
                'test_data' => [
                    'name' => 'test',
                    'is_active' => true
                ]
            ],
            [
                'send_data' => [
                    'name' => 'test',
                    'is_active' => false,
                    'categories_id' => [$categoryId]
                ],
                'test_data' => [
                    'name' => 'test',
                    'is_active' => false
                ]
            ]
        ];

        foreach ($data as $test) {
            $response = $this->assertStore(
                $test['send_data'], 
                $test['test_data']
            );
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $this->assertResource($response, new GeneroResource(
                Genero::find($response->json('data.id'))
            ));

            $response = $this->assertUpdate(
                $test['send_data'], 
                $test['test_data']
            );
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $this->assertResource($response, new GeneroResource(
                Genero::find($response->json('data.id'))
            ));
        }
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
            'genero_id' => $response->json('data.id')
        ]);

        $sendData = [
            'name' => 'test',
            'categories_id' => [$categoriesId[1], $categoriesId[2]]
        ];
        $response = $this->json(
            'PUT',
            route('generos.update', ['genero' => $response->json('data.id')]),
            $sendData
        );

        $this->assertDatabaseMissing('category_genero', [
            'category_id' => $categoriesId[0],
            'genero_id' => $response->json('data.id')
        ]);
        $this->assertDatabaseHas('category_genero', [
            'category_id' => $categoriesId[1],
            'genero_id' => $response->json('data.id')
        ]);
        $this->assertDatabaseHas('category_genero', [
            'category_id' => $categoriesId[2],
            'genero_id' => $response->json('data.id')
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
