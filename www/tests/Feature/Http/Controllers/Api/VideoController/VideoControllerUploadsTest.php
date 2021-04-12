<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Category;
use App\Models\Genero;
use App\Models\Video;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Tests\Traits\TestValidations;
use Tests\Traits\TestUploads;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestUploads;

    public function testInvalidationVideoField()
    {
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            Video::VIDEO_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testInvalidationThumbField()
    {
        $this->assertInvalidationFile(
            'thumb_file',
            'jpg',
            Video::THUMB_FILE_MAX_SIZE,
            'image'
        );
    }

    public function testInvalidationBannerField()
    {
        $this->assertInvalidationFile(
            'banner_file',
            'jpg',
            Video::BANNER_FILE_MAX_SIZE,
            'image'
        );
    }

    public function testInvalidationTrailerField()
    {
        $this->assertInvalidationFile(
            'trailer_file',
            'mp4',
            Video::TRAILER_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }


    public function testStoreWithFiles()
    {
        UploadedFile::fake()->image("image.jpg");
        \Storage::fake();
        $files = $this->getFiles();

        // $category = factory(Category::class)->create();
        // $genero = factory(Genero::class)->create();
        // $genero->categories()->sync($this->category->id);

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + $files
        );

        $response->assertStatus(201);
        $this->assertFilesOnPersist($response, $files);

    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        // $category = factory(Category::class)->create();
        // $genero = factory(Genero::class)->create();
        // $genero->categories()->sync($this->category->id);

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + $files
        );

        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, $files);

        $newFiles = $this->getFiles();

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + $newFiles
        );

        $response->assertStatus(200);
        $this->assertFilesOnPersist(
            $response,
            Arr::except($files, ['thumb_file', 'video_file', 'banner_file', 'trailer_file']) + $newFiles
        );
        $id = $response->json('data.id');
        $video = Video::find($id);
        \Storage::assertMissing($video->relativeFilePath($files['video_file']->hashName()));
        \Storage::assertMissing($video->relativeFilePath($files['thumb_file']->hashName()));
        \Storage::assertMissing($video->relativeFilePath($files['banner_file']->hashName()));
        \Storage::assertMissing($video->relativeFilePath($files['trailer_file']->hashName()));

    }

    protected function assertFilesOnPersist(TestResponse $response, $files)
    {
        $id = $response->json('id') ?? $response->json('data.id');
        $video = Video::find($id);
        $this->assertFilesExistsInStorage($video, $files);
    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create("video_file.mp4"),
            'thumb_file' => UploadedFile::fake()->create("thumb_file.jpg"),
            'banner_file' => UploadedFile::fake()->create("banner_file.jpg"),
            'trailer_file' => UploadedFile::fake()->create("trailer_file.mp4")
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

}
