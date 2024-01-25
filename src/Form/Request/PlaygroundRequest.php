<?php

namespace App\Form\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class PlaygroundRequest
{
    #[Assert\NotBlank]
    #[Assert\Url]
    public string|null $url = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['image'])]
    public string $format = 'image';

    /**
     * @return array{url: string|null, _format: string}
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            '_format' => $this->format,
        ];
    }
}
