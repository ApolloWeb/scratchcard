<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class PlaySession extends Model
{
    use HasFactory, HasUlids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'batch_id',
        'code',
        'status',
        'outcome',
        'prize_tier_id',
        'payout_minor',
        'scratch_pct',
        'scratch_duration',
        'revealed_at',
        'expires_at',
        'server_seed_hash',
        'server_seed_encrypted',
        'client_seed',
        'nonce',
        'ip',
        'ua',
    ];

    protected $casts = [
        'revealed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(GenerationBatch::class, 'batch_id');
    }

    public function prizeTier()
    {
        return $this->belongsTo(PrizeTier::class, 'prize_tier_id');
    }
}
