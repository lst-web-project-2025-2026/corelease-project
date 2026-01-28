<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["name", "category_id", "specs", "status", "supervisor_id"];

    protected $casts = [
        "specs" => "array", // Automatically handles JSON conversion
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, "supervisor_id");
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }
}
