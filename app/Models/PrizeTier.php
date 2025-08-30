<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlid;

class PrizeTier extends Model
{
    use HasFactory, HasUlid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'label',
        'amount_minor',
        'weight',
    ];
}
