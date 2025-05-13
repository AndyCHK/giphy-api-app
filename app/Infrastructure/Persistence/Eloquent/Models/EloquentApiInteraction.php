<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EloquentApiInteraction extends Model
{
    use HasUuids;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'api_interactions';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'service',
        'request_body',
        'response_code',
        'response_body',
        'ip_address',
    ];

    /**
     * Obtiene el usuario que realizó la interacción.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'user_id');
    }
} 