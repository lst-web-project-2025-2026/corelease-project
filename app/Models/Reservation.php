<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "user_id",
        "resource_id",
        "start_date",
        "end_date",
        "user_justification",
        "manager_justification",
        "configuration",
        "status",
        "decided_by",
    ];

    public function decider()
    {
        return $this->belongsTo(User::class, "decided_by");
    }

    protected $casts = [
        "configuration" => "array",
        "start_date" => "date",
        "end_date" => "date",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
