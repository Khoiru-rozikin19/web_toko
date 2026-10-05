<?php

namespace App\Services;

class QrisService
{
    /**
     * Convert static QRIS payload to dynamic QRIS with custom amount.
     * EMVCo QR Code format:
     * Tag 01: Point of Initiation (11 = static, 12 = dynamic)
     * Tag 54: Transaction Amount
     * Tag 58: Country Code (ID)
     * Tag 53: Transaction Currency (360)
     * Tag 63: CRC16-CCITT Checksum
     */
    public function convertStaticToDynamic(string $staticQris, float $amount): string
    {
        $staticQris = trim($staticQris);

        if (empty($staticQris)) {
            return '';
        }

        // Remove existing CRC tag 63 if present at the end (6304XXXX)
        if (str_contains($staticQris, '6304')) {
            $qrisWithoutCrc = substr($staticQris, 0, strrpos($staticQris, '6304'));
        } else {
            $qrisWithoutCrc = $staticQris;
        }

        // 1. Change tag 01 from 010211 to 010212 (Static to Dynamic)
        $step1 = str_replace('010211', '010212', $qrisWithoutCrc);

        // 2. Prepare amount string (Tag 54)
        $formattedAmount = number_format($amount, 0, '', '');
        $amountLength = str_pad(strlen($formattedAmount), 2, '0', STR_PAD_LEFT);
        $amountTag = '54' . $amountLength . $formattedAmount;

        // 3. Inject Tag 54 before Tag 58 (Country Code ID: 5802ID)
        if (str_contains($step1, '5802ID')) {
            $parts = explode('5802ID', $step1);
            // If tag 54 was previously present in the payload, remove it from parts[0]
            $cleanBefore58 = preg_replace('/54\d{2}\d+/', '', $parts[0]);
            $payloadWithAmount = $cleanBefore58 . $amountTag . '5802ID' . $parts[1];
        } else {
            $payloadWithAmount = $step1 . $amountTag;
        }

        // 4. Append Tag 6304 for CRC calculation
        $payloadForCrc = $payloadWithAmount . '6304';

        // 5. Calculate CRC16 CCITT
        $crc = $this->calculateCrc16Ccitt($payloadForCrc);

        return $payloadForCrc . $crc;
    }

    /**
     * Calculate CRC16-CCITT (polynomial 0x1021, initial value 0xFFFF)
     */
    public function calculateCrc16Ccitt(string $data): string
    {
        $crc = 0xFFFF;
        $polynomial = 0x1021;
        $bytes = unpack('C*', $data);

        foreach ($bytes as $b) {
            for ($i = 0; $i < 8; $i++) {
                $bit = (($b >> (7 - $i)) & 1) === 1;
                $c15 = (($crc >> 15) & 1) === 1;
                $crc = ($crc << 1) & 0xFFFF;
                if ($c15 ^ $bit) {
                    $crc ^= $polynomial;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Generate inline SVG QR Code (Simple fallback or high quality SVG)
     */
    public function getQrCodeSvgUrl(string $payload): string
    {
        // We can use a reliable QR API or inline SVG
        return 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' . urlencode($payload);
    }
}
