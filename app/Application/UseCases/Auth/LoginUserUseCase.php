<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Domain\DTOs\Auth\LoginUserDTO;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Domain\Models\User;
use App\Domain\ValueObjects\Email;

final class LoginUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(LoginUserDTO $dto): User
    {
        $user = $this->userRepository->findByEmail(new Email($dto->email));

        if (!$user) {
            throw new InvalidCredentialsException();
        }

        if (!$user->verifyPassword($dto->password)) {
            throw new InvalidCredentialsException();
        }

        return $user;
    }
} 