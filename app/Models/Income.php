<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = [
        'user_id',
        'source',
        'amount',
        'income_date',
        'note',
        'icon',
        'icon_style',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch($query, $value)
    {
        $query->where('source', 'like', "%{$value}%")->orWhere('income_date', 'like', "%{$value}%");
    }
}
