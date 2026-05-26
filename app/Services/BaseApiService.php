<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ResponseApiException;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseApiService
{
    protected string $baseUrl;

    protected ?string $apiKey = null;

    protected ?string $authType = 'bearer';

    protected ?array $tempHeaders = [];

    protected int $timeout = 60;

    public function __call(string $method, array $arguments)
    {
        $allowedMethods = ['get', 'post', 'put', 'delete', 'patch'];

        if (! in_array(mb_strtolower($method), $allowedMethods)) {
            throw new ResponseApiException("Método HTTP {$method} não suportado.");
        }

        try {
            $response = Http::api($this->baseUrl, $this->apiKey, $this->timeout, $this->authType, $this->tempHeaders)
                ->{mb_strtolower($method)}(...$arguments);

            $this->tempHeaders = [];

            return $this->handleResponse($response);
        } catch (Exception $e) {
            $this->tempHeaders = [];

            Log::error('Erro de conexão na API: '.$e->getMessage());

            throw new ResponseApiException("Falha ao conectar com a API: {$e->getMessage()}", 500, $e);
        }
    }

    protected function withHeaders(array $headers): self
    {
        $this->tempHeaders = array_merge($this->tempHeaders, $headers);

        return $this;
    }

    protected function handleResponse(Response $response): mixed
    {
        $data = $response->json();

        if ($response->failed()) {
            $this->logError($response, $data);

            $message = $data['error']['message'] ?? $data['message'] ?? 'Erro desconhecido';

            throw new ResponseApiException("Erro API: {$message}", $response->status());
        }

        return $data['data'] ?? $data;
    }

    protected function logError(Response $response, ?array $data): void
    {
        Log::error('[API Error] '.get_class($this), [
            'url' => $this->baseUrl,
            'status' => $response->status(),
            'body' => $data,
        ]);
    }
}
