<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    protected $table = 'codes';
    protected $fillable = ['phone', 'code', 'created_at'];
    public $timestamps = false;
    protected $dates = ['created_at'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'phone', 'phone');
    }
}