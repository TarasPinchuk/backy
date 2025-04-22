<?php

namespace App\DTO;

use JsonSerializable;

/**
 * @OA\Schema(
 *     schema="UserDTO",
 *     type="object",
 *     title="User Data Transfer Object",
 *     description="Данные пользователя",
 *     required={"id", "username", "email"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="username", type="string", example="johndoe"),
 *     @OA\Property(property="email", type="string", format="email", example="johndoe@example.com")
 * )
 */
class UserDTO implements JsonSerializable
{
    public int $id;
    public string $username;
    public string $email;

    public function __construct(int $id, string $username, string $email)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
        ];
    }
}
