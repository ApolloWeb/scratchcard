<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GameSetting extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'win_numerator',
        'win_denominator',
        'reveal_threshold',
        'min_scratch_time',
        'is_active',
    ];

    protected $casts = [
        'win_numerator' => 'integer',
        'win_denominator' => 'integer',
        'reveal_threshold' => 'integer',
        'min_scratch_time' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::ulid();
            }
        });
    }
}
