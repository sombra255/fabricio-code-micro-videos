<?php

namespace App\Rules;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Validation\Rule;

class GenerosHasCategoriesRule implements Rule
{

    private $categoriesId;
    private $generosId;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $categoriesId)
    {
        $this->categoriesId = array_unique($categoriesId);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(!is_array($value)){
            $value = [];
        }
        $this->generosId = array_unique($value);
        if (!count($this->generosId) || !count($this->categoriesId)){
            return false;
        }
        $categoriesFound = [];
        foreach ($this->generosId as $generoId){
            $rows = $this->getRows($generoId);
            if(!$rows->count()){
                return false;
            }
            array_push($categoriesFound, ...$rows->pluck('category_id')->toArray());
        }
        $categoriesFound = array_unique($categoriesFound);

        if (count($categoriesFound) !== count($this->categoriesId)){
            return false;
        }
        return true;
    
    }

    protected function getRows($generoId): Collection
    {
        return \DB
            ::table('category_genero')
            ->where('genero_id', $generoId)
            ->whereIn('category_id', $this->categoriesId)
            ->get();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        // return 'A genero ID must be related at least a category ID';
        return trans('validation.generos_has_categories');
    }
}
