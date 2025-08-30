<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class PrizeTier extends Model
{
    use HasFactory, HasUlids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'label',
        'amount_minor',
        'weight',
    ];

    public function playSessions()
    {
        return $this->hasMany(PlaySession::class, 'prize_tier_id');
    }
}
