<?php

namespace Tests\Feature\Rules;

use App\Models\Category;
use App\Models\Genero;
use App\Rules\GenerosHasCategoriesRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GenerosHasCategoriesRuleTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    private $categories;
    private $generos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categories = factory(Category::class, 4)->create();
        $this->generos = factory(Genero::class, 2)->create();

        $this->generos[0]->categories()->sync([
            $this->categories[0]->id,
            $this->categories[1]->id
        ]);
        $this->generos[1]->categories()->sync(
            $this->categories[2]->id
        );
    }

    public function testPassesIsValid()
    {
        $rule = new GenerosHasCategoriesRule(
            [
            $this->categories[2]->id
            ]
        );
        $isValid = $rule->passes('', [
            $this->generos[1]->id
        ]);
        $this->assertTrue($isValid);

        $rule = new GenerosHasCategoriesRule(
            [
            $this->categories[0]->id,
            $this->categories[2]->id
            ]
        );
        $isValid = $rule->passes('', [
            $this->generos[0]->id,
            $this->generos[1]->id
        ]);
        $this->assertTrue($isValid);

        $rule = new GenerosHasCategoriesRule(
            [
            $this->categories[0]->id,
            $this->categories[1]->id,
            $this->categories[2]->id
            ]
        );
        $isValid = $rule->passes('', [
            $this->generos[0]->id,
            $this->generos[1]->id
        ]);
        $this->assertTrue($isValid);
    }

    public function testPassesIsNotValid()
    {
        $rule = new GenerosHasCategoriesRule(
            [
            $this->categories[0]->id
            ]
        );
        $isValid = $rule->passes('', [
            $this->generos[0]->id,
            $this->generos[1]->id
        ]);
        $this->assertFalse($isValid);

        $rule = new GenerosHasCategoriesRule(
            [
            $this->categories[3]->id
            ]
        );
        $isValid = $rule->passes('', [
            $this->generos[0]->id
        ]);
        $this->assertFalse($isValid);
    }
}
