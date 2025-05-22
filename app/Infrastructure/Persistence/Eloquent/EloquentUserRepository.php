<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Interfaces\UserRepositoryInterface;
use App\Domain\Models\User;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Password;
use App\Infrastructure\Persistence\Eloquent\Models\EloquentUser;
use DateTime;
use Illuminate\Support\Str;
use mysql_xdevapi\Collection;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(private EloquentUser $model)
    {
    }

    public function findById(string $id): ?User
    {
        $eloquentUser = $this->model->find($id);

        if (!$eloquentUser) {
            return null;
        }

        return $this->toDomainModel($eloquentUser);
    }

    public function findByEmail(Email $email): ?User
    {
        $eloquentUser = $this->model->where('email', (string) $email)->first();

        if (!$eloquentUser) {
            return null;
        }

        return $this->toDomainModel($eloquentUser);
    }

    public function save(User $user): void
    {
        $attributes = [
            'id' => $user->id(),
            'name' => $user->name(),
            'email' => (string) $user->email(),
            'password' => $this->getPasswordFromUser($user),
            'roles' => $user->roles(),
            'email_verified_at' => $user->isEmailVerified() ? $user->emailVerifiedAt() : null,
            'created_at' => $user->createdAt(),
            'updated_at' => $user->updatedAt(),
        ];

        $this->model->create($attributes);
    }

    public function update(User $user): void
    {
        $eloquentUser = $this->model->find($user->id());

        if (!$eloquentUser) {
            throw new \RuntimeException('Usuario no encontrado');
        }

        $attributes = [
            'name' => $user->name(),
            'email' => (string) $user->email(),
            'password' => $this->getPasswordFromUser($user),
            'roles' => $user->roles(),
            'email_verified_at' => $user->isEmailVerified() ? $user->emailVerifiedAt() : null,
            'updated_at' => $user->updatedAt(),
        ];

        $eloquentUser->update($attributes);
    }

    public function delete(string $id): void
    {
        $eloquentUser = $this->model->find($id);

        if (!$eloquentUser) {
            throw new \RuntimeException('Usuario no encontrado');
        }

        $eloquentUser->delete();
    }

    private function toDomainModel(EloquentUser $eloquentUser): User
    {
        return new User(
            (string) $eloquentUser->id,
            new Email($eloquentUser->email),
            new Password($eloquentUser->password, true),
            $eloquentUser->name,
            $eloquentUser->roles ?? [],
            $eloquentUser->email_verified_at ? new DateTime($eloquentUser->email_verified_at->format('Y-m-d H:i:s')) : null,
            new DateTime($eloquentUser->created_at->format('Y-m-d H:i:s')),
            new DateTime($eloquentUser->updated_at->format('Y-m-d H:i:s'))
        );
    }

    private function list() //: Collection
    {
        $users = EloquentUser::all();
        return $users;

    }

    private function getPasswordFromUser(User $user): string
    {
        return $user->getPasswordHash();
    }
}
