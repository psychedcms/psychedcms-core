<?php

declare(strict_types=1);

namespace PsychedCms\Core\Domain\Specification;

use DateTimeImmutable;
use PsychedCms\Core\Content\ContentInterface;

final class IsScheduled extends AbstractSpecification
{
    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof ContentInterface) {
            return false;
        }

        if ($candidate->getStatus() !== 'scheduled') {
            return false;
        }

        $publishedAt = $candidate->getPublishedAt();
        if ($publishedAt === null) {
            return false;
        }

        $now = new DateTimeImmutable();

        return $publishedAt > $now;
    }
}
