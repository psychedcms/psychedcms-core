<?php

declare(strict_types=1);

namespace PsychedCms\Core\Content;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use PsychedCms\Core\Attribute\Field\TextareaField;
use Symfony\Component\Serializer\Annotation\Groups;

trait ContentTrait
{
    use EntityTrait;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Gedmo\Translatable]
    #[TextareaField(label: 'Excerpt', group: 'main', translatable: true)]
    #[Groups(['content:read', 'content:write'])]
    private ?string $excerpt = null;

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
