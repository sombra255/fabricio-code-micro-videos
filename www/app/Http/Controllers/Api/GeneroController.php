<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genero;
use Illuminate\Http\Request;

class GeneroController extends BasicCrudController
{
    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL'
    ];

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $self = $this;
        $obj = \DB::transaction(function () use ($request, $validatedData, $self) {
            $obj = $this->model()::create($validatedData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        $obj->refresh();
        return $obj;
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $self = $this;
        $obj = \DB::transaction(function () use ($request, $validatedData, $self, $obj) {
            $obj->update($validatedData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        return $obj;
    }

    protected function handleRelations($genero, Request $request)
    {
        $genero->categories()->sync($request->get('categories_id'));
    }

    protected function model()
    {
        return Genero::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

    // public function index()
    // {
    //     return Genero::all();
    // }

    // public function store(Request $request)
    // {
    //     $this->validate($request, $this->rules);
    //     $genero = Genero::create($request->all());
    //     $genero->refresh();
    //     return $genero;
    // }

    // public function show(Genero $genero)
    // {
    //     return $genero;
    // }

    // public function update(Request $request, Genero $genero)
    // {
    //     $this->validate($request, $this->rules);
    //     $genero->update($request->all());
    //     return $genero;
    // }

    // public function destroy(Genero $genero)
    // {
    //     $genero->delete();
    //     return response()->noContent();
    // }
}
