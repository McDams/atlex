<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Comptes administrateurs / éditeurs.
 */
final class User extends BaseModel
{
    protected string $table = 'users';

    protected array $fillable = ['name', 'email', 'password', 'role'];

    /**
     * Recherche un utilisateur par email.
     *
     * @return array<string,mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Vérifie les identifiants et retourne l'utilisateur si valides.
     *
     * @return array<string,mixed>|null
     */
    public function attempt(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if ($user !== null && password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    /**
     * Crée un utilisateur en hachant le mot de passe.
     *
     * @param array<string,mixed> $data
     */
    public function createWithHash(array $data): int
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        return $this->create($data);
    }
}
