<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'user_id',
        'parent_id',
        'from',
        'to',
        'amount',
        'is_fraudulent'
    ];
}
