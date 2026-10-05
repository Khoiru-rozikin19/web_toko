<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OkeconnectService
{
    protected string $memberId;
    protected string $pin;
    protected string $password;
    protected string $baseUrl;
    protected bool $sandboxMode;

    public function __construct()
    {
        $this->memberId = (string) Setting::get('okeconnect_member_id', env('OKECONNECT_MEMBER_ID', ''));
        $this->pin = (string) Setting::get('okeconnect_pin', env('OKECONNECT_PIN', ''));
        $this->password = (string) Setting::get('okeconnect_password', env('OKECONNECT_PASSWORD', ''));
        $this->baseUrl = (string) Setting::get('okeconnect_base_url', env('OKECONNECT_BASE_URL', 'https://h2h.okeconnect.com'));
        $this->sandboxMode = Setting::get('okeconnect_sandbox_mode', '1') === '1' || empty($this->memberId);
    }

    public function isConfigured(): bool
    {
        return !empty($this->memberId) && !empty($this->pin) && !empty($this->password);
    }

    /**
     * Submit Topup Paket Data / Pulsa / Token Transaction to Okeconnect
     */
    public function submitTransaction(string $productCode, string $destinationNumber, string $refId): array
    {
        // If in Sandbox Mode or Credentials are empty, simulate instant/processing H2H response
        if ($this->sandboxMode || !$this->isConfigured()) {
            return $this->simulateTransaction($productCode, $destinationNumber, $refId);
        }

        try {
            // Okeconnect H2H endpoint format
            $url = rtrim($this->baseUrl, '/') . '/trx';
            $params = [
                'memberID' => $this->memberId,
                'pin' => $this->pin,
                'password' => $this->password,
                'produk' => $productCode,
                'tujuan' => $destinationNumber,
                'refID' => $refId,
            ];

            $response = Http::timeout(25)->get($url, $params);

            if ($response->successful()) {
                $rawBody = $response->body();
                return $this->parseOkeconnectResponse($rawBody, $refId);
            } else {
                Log::error("Okeconnect HTTP error: {$response->status()} - {$response->body()}");
                return [
                    'success' => false,
                    'status' => 'failed',
                    'message' => 'Gagal terhubung ke server Okeconnect: ' . $response->status(),
                    'raw' => $response->body(),
                ];
            }
        } catch (\Exception $e) {
            Log::error("Okeconnect exception: " . $e->getMessage());
            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'Exception: ' . $e->getMessage(),
                'raw' => null,
            ];
        }
    }

    /**
     * Parse standard Okeconnect response text/json
     */
    protected function parseOkeconnectResponse(string $body, string $refId): array
    {
        // Try JSON parsing first
        $json = json_decode($body, true);
        if (is_array($json)) {
            $status = strtolower($json['status'] ?? 'pending');
            $isSuccess = in_array($status, ['sukses', 'success']);
            $isPending = in_array($status, ['pending', 'proses', 'processing']);

            return [
                'success' => $isSuccess || $isPending,
                'status' => $isSuccess ? 'success' : ($isPending ? 'processing' : 'failed'),
                'provider_trx_id' => $json['trxid'] ?? $json['id'] ?? null,
                'sn_or_token' => $json['sn'] ?? $json['token'] ?? null,
                'message' => $json['pesan'] ?? $json['message'] ?? 'Respons diterima',
                'raw' => $json,
            ];
        }

        // Plain text parse: (e.g. SUKSES, PENDING, GAGAL)
        $upper = strtoupper($body);
        if (str_contains($upper, 'SUKSES') || str_contains($upper, 'BERHASIL')) {
            // Extract SN if available
            preg_match('/SN[:\s]*([0-9A-Za-z\/\.\-]+)/i', $body, $matches);
            $sn = $matches[1] ?? 'SN-' . time();

            return [
                'success' => true,
                'status' => 'success',
                'provider_trx_id' => 'OK-' . time(),
                'sn_or_token' => $sn,
                'message' => $body,
                'raw' => $body,
            ];
        }

        if (str_contains($upper, 'PENDING') || str_contains($upper, 'PROSES') || str_contains($upper, 'ANTRI')) {
            return [
                'success' => true,
                'status' => 'processing',
                'provider_trx_id' => 'OK-' . time(),
                'sn_or_token' => null,
                'message' => 'Transaksi sedang diproses oleh operator',
                'raw' => $body,
            ];
        }

        return [
            'success' => false,
            'status' => 'failed',
            'provider_trx_id' => null,
            'sn_or_token' => null,
            'message' => $body,
            'raw' => $body,
        ];
    }

    /**
     * Check H2H Saldo on Okeconnect
     */
    public function checkH2HBalance(): array
    {
        if ($this->sandboxMode || !$this->isConfigured()) {
            return [
                'success' => true,
                'balance' => 5000000,
                'formatted_balance' => 'Rp 5.000.000 (Mode Simulasi Sandbox)',
            ];
        }

        try {
            $url = rtrim($this->baseUrl, '/') . '/saldo';
            $response = Http::timeout(10)->get($url, [
                'memberID' => $this->memberId,
                'pin' => $this->pin,
                'password' => $this->password,
            ]);

            if ($response->successful()) {
                $body = $response->body();
                // extract number
                preg_match('/(\d+[\.\d]*)/', $body, $matches);
                $balance = isset($matches[1]) ? (float) str_replace('.', '', $matches[1]) : 0;
                return [
                    'success' => true,
                    'balance' => $balance,
                    'formatted_balance' => 'Rp ' . number_format($balance, 0, ',', '.'),
                    'raw' => $body,
                ];
            }
        } catch (\Exception $e) {
            Log::error("Okeconnect balance error: " . $e->getMessage());
        }

        return [
            'success' => false,
            'balance' => 0,
            'formatted_balance' => 'Rp 0 (Gagal terhubung)',
        ];
    }

    /**
     * Simulate transaction in Sandbox Mode
     */
    protected function simulateTransaction(string $productCode, string $destinationNumber, string $refId): array
    {
        $sn = date('YmdHis') . rand(100000, 999999);
        return [
            'success' => true,
            'status' => 'success',
            'provider_trx_id' => 'SANDBOX-' . strtoupper(substr(md5($refId), 0, 8)),
            'sn_or_token' => $sn,
            'message' => "Transaksi Sandbox Sukses. SN: {$sn}",
            'raw' => ['sandbox' => true, 'time' => now()->toIso8601String()],
        ];
    }
}
