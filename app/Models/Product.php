<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category_id',
    ];

<<<<<<< HEAD
=======
    protected $dates = ['deleted_at'];

>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function image()
    {
        return $this->hasMany(Image::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStockStatusAttribute()
    {
        if ($this->stock == 0) {
            return 'out_of_stock';
        } elseif ($this->stock < 10) {
            return 'very_low';
        } elseif ($this->stock < 50) {
            return 'low';
        } else {
            return 'normal';
        }
    }
}
