<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Video;
use App\Models\Category;
use App\Models\Genero;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class BaseVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations;

    protected $video;
    protected $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create([
            'opened' => false
        ]);
        $category = factory(Category::class)->create();
        $genero = factory(Genero::class)->create();
        $genero->categories()->sync($category->id);
        $this->sendData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 2021,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
            'categories_id' => [$category->id],
            'generos_id' => [$genero->id]
        ];
    }

    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }

    protected function assertIfFilesUrlExists(Video $video, TestResponse $response)
    {
        $fileFields = Video::$fileFields;
        $data = $response->json('data');
        $data = array_key_exists(0, $data) ? $data[0] : $data;
        foreach ($fileFields as $field) {
            $file = $video->{$field};
            $filePath = !empty($file) ? \Storage::url($video->relativeFilePath($file)) : null;
            $this->assertEquals($filePath, $data[$field. "_url"]);
        }
    }
}    