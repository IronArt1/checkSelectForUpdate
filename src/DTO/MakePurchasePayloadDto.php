<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
final readonly class MakePurchasePayloadDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 5)]
        #[Assert\GreaterThanOrEqual(0)]
        public int $userId = 0,

        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 5)]
        #[Assert\GreaterThanOrEqual(0)]
        public int $itemId = 0,
    ) {}
}
