<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'specs'];

    protected $casts = [
        'specs' => 'array',
    ];

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }
}
