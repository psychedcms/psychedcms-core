<?php

declare(strict_types=1);

namespace PsychedCms\Core\Content;

interface FeaturableInterface
{
    public function isFeatured(): bool;

    public function setFeatured(bool $featured): static;

    public function getFeaturedOrder(): ?int;

    public function setFeaturedOrder(?int $featuredOrder): static;
}
