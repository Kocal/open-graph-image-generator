<?php

namespace App\Form\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class GenerateOpenGraphImageRequest
{
    #[Assert\NotBlank]
    #[Assert\Url]
    public string|null $url = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['image', 'html'])]
    public string $format = 'image';

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'format' => $this->format,
        ];
    }
}