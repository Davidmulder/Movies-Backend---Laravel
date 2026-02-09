<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MovieApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\RequestException;

class MovieController extends Controller
{
    public function index(Request $request, MovieApiService $movieApiService): JsonResponse
    {
        $page = (int) $request->query('page', 1);

        try {
            $result = $movieApiService->getTitles($page);

            // vou usar json
            return response()->json([
                'page' => $result['page'],
                'nextPage' => $result['nextPage'],
                'hasMore' => $result['hasMore'],
                'total' => $result['total'],
                'count' => $result['count'],
                'items' => $result['items'],

            ]);

        } catch (RequestException $e) {
            // tratar erros
            $status = $e->response?->status() ?? 502;

            return response()->json([
                'message' => 'Erro ao consultar serviço externo (MoviesDatabase).',
                'status' => $status,
                'details' => $e->response?->json() ?? $e->getMessage(),
            ], $status);

        } catch (\Throwable $e) {
            // tratar erros
            return response()->json([
                'message' => 'Erro interno ao processar a requisição.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
