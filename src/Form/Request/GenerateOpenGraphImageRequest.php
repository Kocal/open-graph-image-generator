<?php

namespace App\Form\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class GenerateOpenGraphImageRequest
{

    #[Assert\NotBlank]
    public string|null $headline = null;

    public string|null $subheadline = null;

    public string|null $siteIconUrl = null;

    public string|null $siteName = null;

    public \DateTimeInterface|null $date = null;

    public function toArray() {
        return [
            'headline' => $this->headline,
            'subheadline' => $this->subheadline,
            'siteIconUrl' => $this->siteIconUrl,
            'siteName' => $this->siteName,
            'date' => $this->date->format(DATE_ATOM),
        ];
    }
}