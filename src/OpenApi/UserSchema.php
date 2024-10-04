<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

class UserSchema
{
    #[OA\Property(type: "string")]
    #[Groups(['user:read'])]
    public string $id;

    #[OA\Property(type: "string")]
    #[Groups(['user:read', 'user:write'])]
    public string $email;

    #[OA\Property(type: "string")]
    #[Groups(['user:write'])]
    public string $password;
}