<?php

declare(strict_types=1);

namespace PsychedCms\Core\Content;

use Doctrine\ORM\Mapping as ORM;
use PsychedCms\Core\Attribute\Field\CheckboxField;
use PsychedCms\Core\Attribute\Field\NumberField;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

trait FeaturableTrait
{
    #[ORM\Column(type: 'boolean')]
    #[CheckboxField(label: 'Featured', group: 'meta')]
    #[Groups(['content:read', 'content:write'])]
    private bool $featured = false;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[NumberField(label: 'Featured Order', group: 'meta')]
    #[SerializedName('featured_order')]
    #[Groups(['content:read', 'content:write'])]
    private ?int $featuredOrder = null;

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): static
    {
        $this->featured = $featured;

        return $this;
    }

    public function getFeaturedOrder(): ?int
    {
        return $this->featuredOrder;
    }

    public function setFeaturedOrder(?int $featuredOrder): static
    {
        $this->featuredOrder = $featuredOrder;

        return $this;
    }
}
