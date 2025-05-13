<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class EloquentFavoriteGif extends Model
{
    use HasUuids;

    /** @var string */
    protected $table = 'favorite_gifs';

    /** @var array<int, string> */
    protected $fillable = [
        'user_id',
        'gif_id',
        'alias',
    ];

    protected static function boot()
    {
        parent::boot();
        
        DB::listen(function($query) {
            if (strpos($query->sql, 'favorite_gifs') !== false) {
                logger()->debug('SQL Query:', [
                    'query' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time
                ]);
            }
        });
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'user_id');
    }
} 