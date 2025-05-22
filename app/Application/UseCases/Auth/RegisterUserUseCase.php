<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Domain\DTOs\Auth\RegisterUserDTO;
use App\Domain\Exceptions\UserAlreadyExistsException;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Domain\Models\User;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Password;
use Illuminate\Support\Str;

final class RegisterUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(RegisterUserDTO $dto): User
    {
        if ($this->userRepository->findByEmail(new Email($dto->email))) {
            throw new UserAlreadyExistsException($dto->email);
        }

        $user = new User(
            id: (string) Str::uuid(),
            email: new Email($dto->email),
            password: new Password($dto->password),
            name: $dto->name,
            roles: $dto->roles
        );

        $this->userRepository->save($user);

        return $user;
    }
}
