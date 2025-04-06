<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['category_name'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_category', 'category_id', 'product_id');
    }
}
