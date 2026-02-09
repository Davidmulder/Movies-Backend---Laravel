<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

class MovieApiService
{
    protected string $baseUrl;
    protected string $host;
    protected string $key;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.rapidapi.base_url');
        $this->host    = config('services.rapidapi.host');
        $this->key     = config('services.rapidapi.key');
        $this->timeout = config('services.rapidapi.timeout', 10);
    }

    /**
     * Busca lista de filmes/séries paginados
     */
    public function getTitles(int $page = 1): array
    {
        $page = max(1, $page);

        $response = $this->request('/titles', [
            'page' => $page,
            'info' => 'base_info',
        ]);

        $response->throw();

        $data = $response->json();

        $results = $data['results'] ?? $data['data'] ?? $data['titles'] ?? [];

        $items = collect($results)->map(function ($item) {
            return [
                'id'     => $item['id'] ?? $item['_id'] ?? null,
                'title'  => $item['titleText']['text']
                    ?? $item['originalTitleText']['text']
                    ?? $item['title']
                    ?? null,
                'year'   => $item['releaseYear']['year'] ?? $item['year'] ?? null,
                'poster' => $item['primaryImage']['url'] ?? $item['poster'] ?? $item['image'] ?? null,
            ];
        })

        ->filter(fn ($item) => !empty($item['title']) && !empty($item['poster']))
        ->values()
        ->all();


        $currentPage = (int) ($data['page'] ?? $page);


        $next = $data['next'] ?? $data['nextPage'] ?? $data['next_page'] ?? null;
        $nextPage = is_numeric($next) ? (int) $next : null;

        // total
        $total = $data['entries'] ?? $data['total'] ?? $data['totalResults'] ?? $data['total_results'] ?? null;
        $total = is_numeric($total) ? (int) $total : null;

        if ($nextPage === null && count($items) > 0) {
            $nextPage = $currentPage + 1;
        }

        $hasMore = $nextPage !== null;

        return [
            'page' => $currentPage,
            'nextPage' => $nextPage,
            'hasMore' => $hasMore,
            'total' => $total,
            'count' => count($items),
            'items' => $items,
        ];
    }

    /**
     * Request padrão com headers RapidAPI
     */
    protected function request(string $endpoint, array $query = []): Response
    {
        if (!$this->baseUrl || !$this->host || !$this->key) {
            throw new \Exception('RapidAPI não configurada no .env');
        }

        try {
            return Http::timeout($this->timeout)
                ->withHeaders([
                    'X-RapidAPI-Key'  => $this->key,
                    'X-RapidAPI-Host' => $this->host,
                    'Accept'          => 'application/json',
                ])
                ->get($this->baseUrl . $endpoint, $query);

        } catch (\Throwable $e) {
            Log::error('Erro ao acessar MoviesDatabase API', [
                'endpoint' => $endpoint,
                'query'    => $query,
                'erro'     => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
