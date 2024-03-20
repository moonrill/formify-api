<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function allowedDomains()
    {
        return $this->hasMany(AllowedDomain::class);
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
