<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations;

    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testList()
    {
        factory(CastMember::class, 1)->create();
        $castMembers = CastMember::all();

        $this->assertCount(1, $castMembers);
        $castMemberKey = array_keys($castMembers->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'name','type', 'deleted_at', 'created_at', 'updated_at'
        ],
        $castMemberKey);
    }

    public function testCreate()
    {
        $castMembers = CastMember::create([
            'name' => 'test1',
            'type' => CastMember::TYPE_ACTOR
        ]);
        $castMembers->refresh();
        $this->assertEquals('test1', $castMembers->name);
        $this->assertEquals(CastMember::TYPE_ACTOR, $castMembers->type);
    }

}
