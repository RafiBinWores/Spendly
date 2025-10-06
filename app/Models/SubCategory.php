<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'icon',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeSearch($query, $value)
    {
        $query->where('name', 'like', "%{$value}%")->orWhere('status', 'like', "%{$value}%")->orWhere('created_at', 'like', "%{$value}%");
    }
}
