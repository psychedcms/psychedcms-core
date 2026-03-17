<?php

declare(strict_types=1);

namespace PsychedCms\Core\Content;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use PsychedCms\Core\Attribute\Field\SlugField;
use PsychedCms\Core\Attribute\Field\TextareaField;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

trait ContentTrait
{
    use EntityTrait;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Regex(pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/')]
    #[ApiProperty(identifier: true)]
    #[SlugField(label: 'Slug', group: 'meta')]
    #[Groups(['content:read', 'content:write'])]
    private ?string $slug = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Gedmo\Translatable]
    #[TextareaField(label: 'Excerpt', group: 'main', translatable: true)]
    #[Groups(['content:read', 'content:write'])]
    private ?string $excerpt = null;

    #[Groups(['content:read'])]
    #[SerializedName('id')]
    public function getApiIdentifier(): ?string
    {
        return $this->slug;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): static
    {
        $this->excerpt = $excerpt;

        return $this;
    }
}
