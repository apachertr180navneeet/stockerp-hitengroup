<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConditionMaster extends Model
{
    use HasFactory;


    protected $table = 'condition_master';

    protected $fillable = [
        'name',
        'type',
        'value',
        'status'
    ];
}
