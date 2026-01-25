<?php

declare(strict_types=1);

namespace PsychedCms\Core\Domain\Specification;

use PsychedCms\Core\Content\ContentInterface;

final class IsArchived extends AbstractSpecification
{
    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof ContentInterface) {
            return false;
        }

        return $candidate->getStatus() === 'archived';
    }
}
