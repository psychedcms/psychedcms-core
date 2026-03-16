<?php

declare(strict_types=1);

namespace PsychedCms\Core\Content;

use DateTimeImmutable;
use Symfony\Component\Uid\Ulid;

interface ContentInterface
{
    public function getId(): ?Ulid;

    public function getSlug(): ?string;

    public function getCreatedAt(): ?DateTimeImmutable;

    public function getUpdatedAt(): ?DateTimeImmutable;

    public function getAuthor(): ?object;
}
