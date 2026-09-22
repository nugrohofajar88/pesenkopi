<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Klien ke endpoint PUBLIK simple-pos-backoffice (/api/public/*) - situs ini
 * (pesenkopi) sepenuhnya BFF server-side, tidak ada panggilan API dari browser
 * sama sekali, jadi tidak ada isu CORS. Tidak perlu token - endpoint publik
 * memang didesain tanpa auth (lihat routes/api.php di simple-pos-backoffice).
 */
class PosApi
{
    /** Menu aktif, nested (kategori->produk->modifier). */
    public function menu(): array
    {
        return $this->request('GET', '/public/menu')['data'] ?? [];
    }

    /**
     * Bikin order tamu (self-order). $payload sudah dlm bentuk snake_case
     * sesuai validasi PublicOrderController::store() di backend.
     *
     * @return array{orderNumber:string,total:int}
     */
    public function createOrder(array $payload): array
    {
        return $this->request('POST', '/public/orders', ['json' => $payload])['data'] ?? [];
    }

    protected function request(string $method, string $uri, array $options = []): array
    {
        $response = $this->client()->send($method, ltrim($uri, '/'), $options);

        return $this->decode($response);
    }

    protected function client(): PendingRequest
    {
        return Http::acceptJson()
            ->baseUrl(rtrim((string) config('services.pos_api.base_url'), '/').'/')
            ->timeout(15)
            ->asJson();
    }

    protected function decode(Response $response): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        $body = $response->json();
        $message = data_get($body, 'message', 'Permintaan ke server gagal.');
        $errors = data_get($body, 'errors', []);

        throw new PosApiException($response->status(), $message, is_array($errors) ? $errors : []);
    }
}
