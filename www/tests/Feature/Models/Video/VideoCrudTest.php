<?php

namespace Tests\Feature\Models\Video;

use App\Models\Category;
use App\Models\Video;
use App\Models\Genero;
use Illuminate\Database\QueryException;

class VideoCrudTest extends BaseVideoTestCase
{
    private $fileFieldsData = [];

    protected function setUp(): void   
    {
        parent::setUp();
        foreach (Video::$fileFields as $field) {
            $this->fileFieldsData[$field] = "$field.test";
        }
    }

    public function testList()
    {
        factory(Video::class)->create();
        $videos = Video::all();
        $this->assertCount(1, $videos);
        $videoKeys = array_keys($videos->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'title',
                'description',
                'year_launched',
                'opened',
                'rating',
                'duration',
                'video_file',
                'thumb_file',
                'banner_file',
                'trailer_file',
                'created_at',
                'updated_at',
                'deleted_at'
            ],
            $videoKeys
        );
    }

    public function testCreateWithBasicFields()
    {

        $video = Video::create($this->data + $this->fileFieldsData);
        $video->refresh();

        $this->assertEquals(36, strlen($video->id));
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + $this->fileFieldsData + ['opened' => false]);

        $video = Video::create($this->data + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', ['opened' => true]);
    }

    public function testCreateWithRelations()
    {
        $category = factory(Category::class)->create();
        $genero = factory(Genero::class)->create();
        $video = Video::create($this->data + [
            'categories_id' => [$category->id],
            'generos_id' => [$genero->id]
            ]
        );

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenero($video->id, $genero->id);
    }

    public function testRollbackCreate()
    {

        $hasError = false;
        try {
            Video::create([
                'title' => 'title',
                'description' => 'description',
                'year_launched' => 2021,
                'rating' => Video::RATING_LIST[0],
                'duration' => 90,
                'categories_id' => [0, 1, 2]
            ]);
        } catch (QueryException $exception) {
            $this->assertCount(0, Video::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $video = factory(Video::class)->create();
        $oldTitle = $video->title;

        try {
           $video->update([
                'title' => 'title',
                'description' => 'description',
                'year_launched' => 2021,
                'rating' => Video::RATING_LIST[0],
                'duration' => 90,
                'categories_id' => [0, 1, 2]
            ]);
        } catch (QueryException $exception) {
            $this->assertDatabaseHas('videos', [
                'title' => $oldTitle
            ]);
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testUpdateWithBasicFields()
    {
        $video = factory(Video::class)->create(
            ['opened' => false]
        );

        $video->update($this->data + $this->fileFieldsData);
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $video->update($this->data + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + $this->fileFieldsData + ['opened' => true]);
    }

    public function testUpdateWithRelations()
    {
        $category = factory(Category::class)->create();
        $genero = factory(Genero::class)->create();
        $video = factory(Video::class)->create();
        $video->update($this->data + [
            'categories_id' => [$category->id],
            'generos_id' => [$genero->id]
            ]
        );

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenero($video->id, $genero->id);
    }

    protected function assertHasCategory($videoId, $categoryId)            
    {
        $this->assertDatabaseHas('category_video', [
            'video_id' => $videoId,
            'category_id' => $categoryId
        ]);
    }

    protected function assertHasGenero($videoId, $generoId)            
    {
        $this->assertDatabaseHas('genero_video', [
            'video_id' => $videoId,
            'genero_id' => $generoId
        ]);
    }

    public function testHandleRelations()
    {
        $video = factory(Video::class)->create();
        Video::handleRelations($video, []);
        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->generos);

        $category = factory(Category::class)->create();
        Video::handleRelations($video, [
            'categories_id' => [$category->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->categories);

        $genero = factory(Genero::class)->create();
        Video::handleRelations($video, [
            'generos_id' => [$genero->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->generos);

        $video->categories()->delete();
        $video->generos()->delete();

        Video::handleRelations($video, [
            'categories_id' => [$category->id],
            'generos_id' => [$genero->id]
        ]);
        $this->assertCount(1, $video->categories);
        $this->assertCount(1, $video->generos);
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $video = factory(Video::class)->create();
        Video::handleRelations($video, [
            'categories_id' => [$categoriesId[0]]
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);

        Video::handleRelations($video, [
            'categories_id' => [$categoriesId[1], $categoriesId[2]]
        ]);

        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[1],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[2],
            'video_id' => $video->id
        ]);

    }

    public function testSyncGeneros()
    {
        $generosId = factory(Genero::class, 3)->create()->pluck('id')->toArray();
        $video = factory(Video::class)->create();
        Video::handleRelations($video, [
            'generos_id' => [$generosId[0]]
        ]);

        $this->assertDatabaseHas('genero_video', [
            'genero_id' => $generosId[0],
            'video_id' => $video->id
        ]);

        Video::handleRelations($video, [
            'generos_id' => [$generosId[1], $generosId[2]]
        ]);

        $this->assertDatabaseMissing('genero_video', [
            'genero_id' => $generosId[0],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genero_video', [
            'genero_id' => $generosId[1],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genero_video', [
            'genero_id' => $generosId[2],
            'video_id' => $video->id
        ]);

    }

    public function testDelete()
    {
        $video = factory(Video::class)->create();
        $video->delete();
        $this->assertNull(Video::find($video->id));

        $video->restore();
        $this->assertNotNull(Video::find($video->id));
    }
}
