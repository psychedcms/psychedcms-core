<?php

declare(strict_types=1);

namespace PsychedCms\Core\Domain\Specification;

use DateTimeImmutable;
use PsychedCms\Core\Content\ContentInterface;

final class IsPublished extends AbstractSpecification
{
    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof ContentInterface) {
            return false;
        }

        if ($candidate->getStatus() !== 'published') {
            return false;
        }

        $publishedAt = $candidate->getPublishedAt();
        if ($publishedAt === null) {
            return false;
        }

        $now = new DateTimeImmutable();
        if ($publishedAt > $now) {
            return false;
        }

        $depublishedAt = $candidate->getDepublishedAt();
        if ($depublishedAt !== null && $depublishedAt <= $now) {
            return false;
        }

        return true;
    }
}
