<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ["author", "title", "quantity", "isbn", "price"];

    protected $casts = [
        "price" => "decimal:2",
        "quantity" => "integer",
    ];

    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class)
            ->withPivot(["quantity"])
            ->withTimestamps();
    }
}
