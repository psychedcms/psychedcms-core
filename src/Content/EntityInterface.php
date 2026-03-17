<?php

declare(strict_types=1);

namespace PsychedCms\Core\Content;

use DateTimeImmutable;
use Symfony\Component\Uid\Ulid;

interface EntityInterface
{
    public function getId(): ?Ulid;

    public function getCreatedAt(): ?DateTimeImmutable;

    public function getUpdatedAt(): ?DateTimeImmutable;
}
