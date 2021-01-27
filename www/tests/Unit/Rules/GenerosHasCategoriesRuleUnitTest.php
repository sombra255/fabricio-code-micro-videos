<?php
declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\GenerosHasCategoriesRule;
use Mockery\MockInterface;
use Tests\TestCase;

class GenerosHasCategoriesRuleUnitTest extends TestCase
{
    public function testCategoriesIdField()
    {
        $rule = new GenerosHasCategoriesRule(
            [1, 1, 2, 2]
        );
        $reflectionClass = new \ReflectionClass(GenerosHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('categoriesId');
        $reflectionProperty->setAccessible(true);

        $categoriesId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1, 2], $categoriesId);
    }

    public function testGenerosIdValue()
    {
        $rule = $this->createRuleMock([]);

        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturnNull();

        $rule->passes('', [1, 1, 2, 2]);

        $reflectionClass = new \ReflectionClass(GenerosHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('generosId');
        $reflectionProperty->setAccessible(true);

        $generosId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1, 2], $generosId);
    }

    public function testPassesReturnsFalseWhenCategoriesOrGenerosIsArrayEmpty()
    {
        $rule = $this->createRuleMock([1]);
        $this->assertFalse($rule->passes('', []));

        $rule = $this->createRuleMock([]);
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenGetRowsIsEmpty()
    {
        $rule = $this->createRuleMock([]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect());
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenHasCategoriesWithoutGeneros()
    {
        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect(['category_id' => 1]));
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesIsValid()
    {
        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect([
                ['category_id' => 1],
                ['category_id' => 2]
            ]));

            $rule = $this->createRuleMock([1, 2]);
            $rule
                ->shouldReceive('getRows')
                ->withAnyArgs()
                ->andReturn(collect([
                    ['category_id' => 1],
                    ['category_id' => 2],
                    ['category_id' => 1],
                    ['category_id' => 2]
                ]));
    }

    protected function createRuleMock(array $categoriesId): MockInterface
    {
        return \Mockery::mock(GenerosHasCategoriesRule::class, [$categoriesId])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }
}
