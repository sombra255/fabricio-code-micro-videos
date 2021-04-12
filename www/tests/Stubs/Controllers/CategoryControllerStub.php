<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Tests\Stubs\Models\CategoryStub;
use Tests\Stubs\Resources\CategoryStubResource;

class CategoryControllerStub extends BasicCrudController
{
    protected function model()
    {
        return CategoryStub::class;
    }

    protected function rulesStore()
    {
        return [
            'name' => 'required|max:255',
            'description' => 'nullable'
        ];
    }

    protected function rulesUpdate()
    {
        return [
            'name' => 'required|max:255',
            'description' => 'nullable'
        ];
    }

    protected function resource()
    {
        return CategoryStubResource::class;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }
}
