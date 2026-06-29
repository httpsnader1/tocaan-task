<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

trait JsonResponseTrait
{
    public function apiResponse($message = '', $data = [], $status = 200, $headers = []): JsonResponse
    {
        return response()->json(
            [
                'status' => $status,
                'message' => $message,
                'data' => $this->formatData($data),
            ],
            $status,
            $headers
        );
    }

    public function formatData(mixed $data): mixed
    {
        if ($data instanceof LengthAwarePaginator) {
            return $this->formatPagination($data);
        }

        if ($data instanceof AnonymousResourceCollection) {
            return $this->formatResourceCollection($data);
        }

        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if ($value instanceof LengthAwarePaginator) {
                $data[$key] = $this->formatPagination($value);
            }

            if ($value instanceof AnonymousResourceCollection) {
                $data[$key] = $this->formatResourceCollection($value);
            }
        }

        return $data;
    }

    private function formatPagination(LengthAwarePaginator $paginator, ?AnonymousResourceCollection $resourceCollection = NULL): array
    {
        return [
            'data' => $resourceCollection?->collection ?? $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
        ];
    }

    private function formatResourceCollection(AnonymousResourceCollection $collection): array
    {
        if ($collection->resource instanceof LengthAwarePaginator) {
            return $this->formatPagination($collection->resource, $collection);
        }

        return [
            'data' => $collection,
        ];
    }
}
