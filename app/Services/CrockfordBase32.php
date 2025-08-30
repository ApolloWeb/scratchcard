<?php

namespace App\Services;

class CrockfordBase32
{
    // Crockford's Base32 alphabet (no I, L, O, U)
    private const ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    /**
     * Generate a random Crockford Base32 string of the requested length.
     *
     * This implementation generates enough random bytes to cover length*5 bits
     * (each base32 char encodes 5 bits) and then maps 5-bit groups to the
     * Crockford alphabet. Lengths up to 12 are safe on 64-bit platforms.
     *
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public static function generate(int $length): string
    {
        if ($length <= 0) {
            return '';
        }

        $bitsNeeded = $length * 5;
        $bytesNeeded = intdiv($bitsNeeded + 7, 8); // ceil(bits/8)

        // Get cryptographically secure random bytes
        $bytes = random_bytes($bytesNeeded);

        // Convert to bit string
        $bitStr = '';
        for ($i = 0, $len = strlen($bytes); $i < $len; $i++) {
            $bitStr .= str_pad(decbin(ord($bytes[$i])), 8, '0', STR_PAD_LEFT);
        }

        // Truncate to needed bits
        $bitStr = substr($bitStr, 0, $bitsNeeded);

        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $chunk = substr($bitStr, $i * 5, 5);
            $val = bindec($chunk);
            $out .= self::ALPHABET[$val];
        }

        return $out;
    }
}
