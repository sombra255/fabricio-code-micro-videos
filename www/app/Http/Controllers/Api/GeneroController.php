<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genero;
use Illuminate\Http\Request;

class GeneroController extends Controller
{
    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean'
    ];

    public function index()
    {
        return Genero::all();
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->rules);
        return Genero::create($request->all());
    }

    public function show(Genero $genero)
    {
        return $genero;
    }

    public function update(Request $request, Genero $genero)
    {
        $this->validate($request, $this->rules);
        $genero->update($request->all());
        return $genero;
    }

    public function destroy(Genero $genero)
    {
        $genero->delete();
        return response()->noContent();
    }
}
