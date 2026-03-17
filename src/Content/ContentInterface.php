<?php

declare(strict_types=1);

namespace PsychedCms\Core\Content;

interface ContentInterface extends EntityInterface
{
    public function getExcerpt(): ?string;

    public function getAuthor(): ?object;
}
