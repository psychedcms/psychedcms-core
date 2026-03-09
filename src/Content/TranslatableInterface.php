<?php

declare(strict_types=1);

namespace PsychedCms\Core\Content;

interface TranslatableInterface
{
    public function getLocale(): ?string;

    public function setTranslatableLocale(?string $locale): static;
}
