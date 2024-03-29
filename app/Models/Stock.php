<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;


    protected $table = 'stock';

    protected $fillable = [
        'stock_date',
        'vendor_id',
        'branch_id',
        'status',
        'total_amount',
        'qty'
    ];
}
