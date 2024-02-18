<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
