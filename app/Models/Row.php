<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Row extends Model
{
    use HasFactory;
    protected $fillable = ['excel_id','name','date'];

    protected $casts = [
        'date' => 'date',
    ];

}
