<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class EloquentUser extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $table = 'users';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'roles',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'roles' => 'array',
    ];

    /**
     * Método estático personalizado para encontrar un usuario por ID y registrar el proceso
     */
    public static function find($id)
    {
        logger()->debug('Buscando usuario por ID', [
            'id' => $id,
            'id_type' => gettype($id),
        ]);

        // Llamamos al método find original pero a través de la clase padre para evitar recursión
        $query = static::query();
        $user = $query->find($id);

        if ($user) {
            logger()->debug('Usuario encontrado', [
                'id' => $id,
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);
        } else {
            logger()->debug('Usuario NO encontrado', [
                'id' => $id,
            ]);
        }

        return $user;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
