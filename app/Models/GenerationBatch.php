<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlid;

class GenerationBatch extends Model
{
    use HasFactory, HasUlid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'requested_count',
        'created_count',
        'status',
        'win_numerator',
        'win_denominator',
        'code_length',
        'settings_snapshot',
        'error',
    ];

    protected $casts = [
        'settings_snapshot' => 'array',
    ];

    public function playSessions()
    {
        return $this->hasMany(PlaySession::class, 'batch_id');
    }
}
