<?php

namespace Tests\Unit\Models;

use App\Models\Genero;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class GeneroUnitTest extends TestCase
{
    private $genero;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genero = new Genero();
    }

    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testIfUseTraits()
    {
        $traits = [
            SoftDeletes::class,
            Uuid::class
        ];
        $generoTraits = array_keys(class_uses(Genero::class));
        $this->assertEquals($traits, $generoTraits);
    }

    public function testGetFillable()
    {
        $fillable = ['name', 'is_active'];
        $this->assertEquals($fillable, $this->genero->getFillable());
    }

    public function testGetDates()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach($dates as $date){
            $this->assertContains($date, $this->genero->getDates());
        }
        $this->assertCount(count($dates), $this->genero->getDates());
    }

    public function testCastAttribute()
    {
        $casts = [
            'is_active' => 'boolean'
        ];
        $this->assertEquals($casts, $this->genero->getCasts());
    }

    public function testGetIncrementing()
    {
        $this->assertFalse($this->genero->getIncrementing());
    }
}
