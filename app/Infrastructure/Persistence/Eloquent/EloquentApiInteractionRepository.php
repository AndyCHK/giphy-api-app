<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Interfaces\ApiInteractionRepositoryInterface;
use App\Domain\Models\ApiInteraction;
use App\Infrastructure\Persistence\Eloquent\Models\EloquentApiInteraction;
use DateTime;
use Illuminate\Support\Facades\Log;


final class EloquentApiInteractionRepository implements ApiInteractionRepositoryInterface
{
    public function __construct(
        private readonly EloquentApiInteraction $model
    ) {
    }

    /**
     * @param ApiInteraction $apiInteraction
     */
    public function save(ApiInteraction $apiInteraction): void
    {
        try {
            $attributes = [
                'id' => $apiInteraction->id(),
                'user_id' => $apiInteraction->userId(),
                'service' => $apiInteraction->service(),
                'request_body' => $apiInteraction->requestBody(),
                'response_code' => $apiInteraction->responseCode(),
                'response_body' => $apiInteraction->responseBody(),
                'ip_address' => $apiInteraction->ipAddress(),
                'created_at' => $apiInteraction->createdAt(),
                'updated_at' => $apiInteraction->updatedAt(),
            ];

            $this->model->create($attributes);
        } catch (\Throwable $e) {
            Log::error('Error al guardar interacción API', [
                'message' => $e->getMessage(),
                'interaction_id' => $apiInteraction->id(),
            ]);
            throw $e;
        }
    }

    /**
     * @param string $id
     * @return ApiInteraction|null
     */
    public function findById(string $id): ?ApiInteraction
    {
        $eloquentApiInteraction = $this->model->find($id);

        if (!$eloquentApiInteraction) {
            return null;
        }

        return $this->toDomainModel($eloquentApiInteraction);
    }

    /**
     * @param string $userId
     * @return array
     */
    public function findByUserId(string $userId): array
    {
        $eloquentApiInteractions = $this->model->where('user_id', $userId)->get();

        return $eloquentApiInteractions->map(function ($eloquentApiInteraction) {
            return $this->toDomainModel($eloquentApiInteraction);
        })->toArray();
    }

    /**
     * @param string $service
     * @return array
     */
    public function findByService(string $service): array
    {
        $eloquentApiInteractions = $this->model->where('service', $service)->get();

        return $eloquentApiInteractions->map(function ($eloquentApiInteraction) {
            return $this->toDomainModel($eloquentApiInteraction);
        })->toArray();
    }

    /**
     * @param int $responseCode 
     * @return array
     */
    public function findByResponseCode(int $responseCode): array
    {
        $eloquentApiInteractions = $this->model->where('response_code', $responseCode)->get();

        return $eloquentApiInteractions->map(function ($eloquentApiInteraction) {
            return $this->toDomainModel($eloquentApiInteraction);
        })->toArray();
    }

    /**
     * @param EloquentApiInteraction $eloquentApiInteraction
     * @return ApiInteracti
     */
    private function toDomainModel(EloquentApiInteraction $eloquentApiInteraction): ApiInteraction
    {
        return new ApiInteraction(
            (string) $eloquentApiInteraction->id,
            $eloquentApiInteraction->user_id,
            $eloquentApiInteraction->service,
            $eloquentApiInteraction->request_body,
            $eloquentApiInteraction->response_code,
            $eloquentApiInteraction->response_body,
            $eloquentApiInteraction->ip_address,
            new DateTime($eloquentApiInteraction->created_at->format('Y-m-d H:i:s')),
            new DateTime($eloquentApiInteraction->updated_at->format('Y-m-d H:i:s'))
        );
    }
} 