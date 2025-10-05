<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'source',
        'amount',
        'expense_date',
        'note',
        'icon',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeSearch($query, $value)
    {
        $query->where('source', 'like', "%{$value}%")->orWhere('expense_date', 'like', "%{$value}%");
    }
}
