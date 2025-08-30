<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaySession extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'campaign_id',
        'batch_id',
        'code',
        'masked_token',
        'status',
        'outcome',
        'prize_tier_id',
        'payout_minor',
        'scratch_pct',
        'scratch_duration',
        'revealed_at',
        'expires_at',
        'server_seed_hash',
        'client_seed',
        'nonce',
        'ip',
        'ua',
        'fraud_score',
        'referrer',
        'session_data',
    ];

    protected $casts = [
        'payout_minor' => 'integer',
        'scratch_pct' => 'integer',
        'scratch_duration' => 'integer',
        'revealed_at' => 'datetime',
        'expires_at' => 'datetime',
        'nonce' => 'integer',
        'fraud_score' => 'integer',
        'session_data' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::ulid();
            }
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(GenerationBatch::class, 'batch_id');
    }

    public function prizeTier(): BelongsTo
    {
        return $this->belongsTo(PrizeTier::class);
    }
}
