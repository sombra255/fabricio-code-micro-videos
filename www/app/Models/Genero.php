<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Genero
 *
 * @property string $id
 * @property string $name
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Genero newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Genero newQuery()
 * @method static \Illuminate\Database\Query\Builder|Genero onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Genero query()
 * @method static \Illuminate\Database\Eloquent\Builder|Genero whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Genero whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Genero whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Genero whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Genero whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Genero whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Genero withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Genero withoutTrashed()
 * @mixin \Eloquent
 */
class Genero extends Model
{
    use SoftDeletes, \App\Models\Traits\Uuid;
    protected $fillable = ['name', 'is_active'];
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'is_active' => 'boolean'
    ];
}
