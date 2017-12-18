<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $fillable = ['transaction_id', 'user_id', 'type', 'time', 'amount', 'addresses'];
    protected $dates = ['time'];
    public $timestamps = FALSE;
}