<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "name",
        "email",
        "password",
        "profession",
        "user_justification",
        "admin_justification",
        "status",
        "decided_by",
    ];

    public function decider()
    {
        return $this->belongsTo(User::class, "decided_by");
    }
}
