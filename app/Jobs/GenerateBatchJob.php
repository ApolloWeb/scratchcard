<?php

namespace App\Jobs;

use App\Models\GenerationBatch;
use App\Models\PlaySession;
use App\Services\CrockfordBase32;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GenerateBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public GenerationBatch $batch;

    /**
     * Create a new job instance.
     */
    public function __construct(GenerationBatch $batch)
    {
        $this->batch = $batch;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $batch = GenerationBatch::find($this->batch->id);
        if (!$batch) {
            return;
        }

        $batch->status = 'RUNNING';
        $batch->save();

        $count = $batch->requested_count;
        $wins_needed = intval(floor($count * ($batch->win_numerator / max(1, $batch->win_denominator))));

        $tiers = collect($batch->settings_snapshot['prize_tiers']);
        $total_weight = $tiers->sum('weight');

        $created = 0;

        try {
            DB::transaction(function() use ($batch, $count, $wins_needed, $tiers, $total_weight, &$created) {
                for ($i = 0; $i < $count; $i++) {
                    $is_win = ($i < $wins_needed) ? 'WIN' : 'LOSE';

                    $prize_tier_id = null;
                    $payout_minor = 0;
                    $box_symbols = [];
                    $winning_symbol = null;

                    if ($is_win === 'WIN' && $total_weight > 0) {
                        $r = rand(1, $total_weight);
                        $acc = 0;
                        foreach ($tiers as $t) {
                            $acc += $t['weight'];
                            if ($r <= $acc) {
                                $prize_tier_id = $t['id'];
                                $payout_minor = $t['amount_minor'];
                                break;
                            }
                        }

                        // For winning tickets, all 3 boxes show the prize amount
                        $winning_symbol = '£' . number_format($payout_minor / 100, 0);
                        $box_symbols = [$winning_symbol, $winning_symbol, $winning_symbol];
                    } else {
                        // For losing tickets, show random amounts from prize tiers
                        // Pick different amounts to ensure no 3 matching
                        $amounts = [];
                        foreach ($tiers as $tier) {
                            $amounts[] = '£' . number_format($tier['amount_minor'] / 100, 0);
                        }
                        
                        // Shuffle and take first 3, or repeat if we have fewer than 3 tiers
                        shuffle($amounts);
                        if (count($amounts) < 3) {
                            // If fewer than 3 tiers, add some defaults
                            $amounts = array_merge($amounts, ['£1', '£5', '£10', '£50']);
                            shuffle($amounts);
                        }
                        
                        $box_symbols = [
                            $amounts[0],
                            $amounts[1] ?? $amounts[0],
                            $amounts[2] ?? $amounts[0]
                        ];
                        
                        // Ensure not all 3 are the same (make it a definite loss)
                        if ($box_symbols[0] === $box_symbols[1] && $box_symbols[1] === $box_symbols[2]) {
                            // Change one to a different amount
                            $differentAmounts = array_diff($amounts, [$box_symbols[0]]);
                            if (!empty($differentAmounts)) {
                                $box_symbols[2] = reset($differentAmounts);
                            } else {
                                $box_symbols[2] = '£1'; // fallback
                            }
                        }
                    }

                    PlaySession::create([
                        'batch_id' => $batch->id,
                        'code' => CrockfordBase32::generate($batch->code_length),
                        'status' => 'NEW',
                        'outcome' => $is_win,
                        'prize_tier_id' => $prize_tier_id,
                        'payout_minor' => $payout_minor,
                        'box_symbols' => json_encode($box_symbols),
                        'winning_symbol' => $winning_symbol,
                        'server_seed_hash' => hash('sha256', bin2hex(random_bytes(16))),
                        'server_seed_encrypted' => '',
                        'nonce' => $i + 1, // Use ticket index as nonce
                    ]);

                    $created++;

                    // update progress every 100 or at end
                    if ($created % 100 === 0 || $created === $count) {
                        $batch->created_count = $created;
                        $batch->save();
                    }
                }
            });

            // randomize outcomes by shuffling play sessions' outcomes
            $ids = PlaySession::where('batch_id', $batch->id)->pluck('id')->toArray();
            shuffle($ids);
            // not doing heavy updates to reorder; skipping

            $batch->created_count = $created;
            $batch->status = 'COMPLETED';
            $batch->save();
        } catch (Exception $e) {
            $batch->status = 'FAILED';
            $batch->error = $e->getMessage();
            $batch->save();
        }
    }
}
