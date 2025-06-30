<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealizedPnl extends Model
{
        protected $fillable = [
        'user_id',
        'total_realized_pnl'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
