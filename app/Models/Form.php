<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function user()
    {
        $this->belongsTo(User::class);
    }

    public function questions()
    {
        $this->hasMany(Question::class);
    }

    public function allowedDomains()
    {
        $this->hasMany(AllowedDomain::class);
    }
}
