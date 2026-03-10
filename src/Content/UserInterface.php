<?php

declare(strict_types=1);

namespace PsychedCms\Core\Content;

interface UserInterface
{
    public function getEmail(): ?string;

    public function getDisplayName(): ?string;

    public function getRoles(): array;

    public function getLocale(): ?string;
}
