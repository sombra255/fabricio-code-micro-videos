<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use App\Http\Controllers\Controller;
use App\Http\Resources\VideoResource;
use App\Rules\GenerosHasCategoriesRule;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{

    private $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'generos_id' => [
                'required',
                'array',
                'exists:generos,id,deleted_at,NULL'
            ],
            // 'video_file' => 'required',
            'video_file' => 'mimetypes:video/mp4|max:'. Video::VIDEO_FILE_MAX_SIZE,
            'thumb_file' => 'image|max:'. Video::THUMB_FILE_MAX_SIZE,
            'banner_file' => 'image|max:'. Video::BANNER_FILE_MAX_SIZE,
            'trailer_file' => 'mimetypes:video/mp4|max:'. Video::TRAILER_FILE_MAX_SIZE
        ];
    }

    public function store(Request $request)
    {
        $this->addRuleIfGeneroHasCategories($request);
        $validatedData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $this->addRuleIfGeneroHasCategories($request);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $obj->update($validatedData);
        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function addRuleIfGeneroHasCategories(Request $request)
    {
        $categoriesId = $request->get('categories_id');
        $categoriesId = is_array($categoriesId) ? $categoriesId : [];
        $this->rules['generos_id'][] = new GenerosHasCategoriesRule(
            $categoriesId
        );
    }

    // protected function handleRelations($video, Request $request)
    // {
    //     $video->categories()->sync($request->get('categories_id'));
    //     $video->generos()->sync($request->get('generos_id'));
    // }

    protected function model()
    {
        return Video::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource(){
        return VideoResource::class;
    }
}
