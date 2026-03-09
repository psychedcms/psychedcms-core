<?php

declare(strict_types=1);

namespace PsychedCms\Core\Content;

use Gedmo\Mapping\Annotation as Gedmo;

trait TranslatableTrait
{
    #[Gedmo\Locale]
    private ?string $locale = null;

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setTranslatableLocale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }
}
