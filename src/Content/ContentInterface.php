<?php

declare(strict_types=1);

namespace PsychedCms\Core\Content;

use DateTimeImmutable;

interface ContentInterface
{
    public function getId(): ?int;

    public function getSlug(): ?string;

    public function getStatus(): string;

    public function getCreatedAt(): ?DateTimeImmutable;

    public function getUpdatedAt(): ?DateTimeImmutable;

    public function getPublishedAt(): ?DateTimeImmutable;

    public function getDepublishedAt(): ?DateTimeImmutable;

    public function getAuthor(): ?object;
}
