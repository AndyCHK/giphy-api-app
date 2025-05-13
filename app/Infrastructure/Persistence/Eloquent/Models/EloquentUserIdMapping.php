<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EloquentUserIdMapping extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'user_id_mappings';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_uuid',
    ];

    /**
     * Obtiene el usuario al que pertenece este mapeo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'user_uuid', 'id');
    }

    /**
     * Busca un mapeo por UUID de usuario
     * 
     * @param string $uuid
     * @return EloquentUserIdMapping|null
     */
    public static function findByUuid(string $uuid): ?EloquentUserIdMapping
    {
        return static::where('user_uuid', $uuid)->first();
    }

    /**
     * Crea un nuevo mapeo para un usuario si no existe
     * 
     * @param string $uuid
     * @return EloquentUserIdMapping
     */
    public static function createForUserUuid(string $uuid): EloquentUserIdMapping
    {
        $mapping = static::findByUuid($uuid);
        
        if ($mapping) {
            return $mapping;
        }
        
        return static::create(['user_uuid' => $uuid]);
    }
}
