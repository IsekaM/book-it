<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ["author", "title", "quantity", "isbn", "price"];

    protected $casts = [
        "price" => "decimal:2",
        "quantity" => "integer",
    ];
}
