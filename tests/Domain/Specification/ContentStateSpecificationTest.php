<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Domain\Specification;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Content\ContentInterface;
use PsychedCms\Core\Content\ContentTrait;
use PsychedCms\Core\Domain\Specification\IsArchived;
use PsychedCms\Core\Domain\Specification\IsDraft;
use PsychedCms\Core\Domain\Specification\IsPublished;
use PsychedCms\Core\Domain\Specification\IsScheduled;

final class ContentStateSpecificationTest extends TestCase
{
    public function testIsPublishedReturnsTrueWhenPublishedAndNotDepublished(): void
    {
        $content = $this->createContent()
            ->setStatus('published')
            ->setPublishedAt(new DateTimeImmutable('-1 hour'));

        $spec = new IsPublished();

        $this->assertTrue($spec->isSatisfiedBy($content));

        $contentWithFutureDepublish = $this->createContent()
            ->setStatus('published')
            ->setPublishedAt(new DateTimeImmutable('-1 hour'))
            ->setDepublishedAt(new DateTimeImmutable('+1 hour'));

        $this->assertTrue($spec->isSatisfiedBy($contentWithFutureDepublish));
    }

    public function testIsPublishedReturnsFalseWhenDepublishedInPast(): void
    {
        $content = $this->createContent()
            ->setStatus('published')
            ->setPublishedAt(new DateTimeImmutable('-2 hours'))
            ->setDepublishedAt(new DateTimeImmutable('-1 hour'));

        $spec = new IsPublished();

        $this->assertFalse($spec->isSatisfiedBy($content));
    }

    public function testIsDraftReturnsTrueOnlyWhenStatusIsDraft(): void
    {
        $spec = new IsDraft();

        $draftContent = $this->createContent()->setStatus('draft');
        $this->assertTrue($spec->isSatisfiedBy($draftContent));

        $publishedContent = $this->createContent()->setStatus('published');
        $this->assertFalse($spec->isSatisfiedBy($publishedContent));

        $archivedContent = $this->createContent()->setStatus('archived');
        $this->assertFalse($spec->isSatisfiedBy($archivedContent));
    }

    public function testIsScheduledReturnsTrueWhenStatusIsScheduledAndPublishedAtInFuture(): void
    {
        $spec = new IsScheduled();

        $scheduledContent = $this->createContent()
            ->setStatus('scheduled')
            ->setPublishedAt(new DateTimeImmutable('+1 hour'));
        $this->assertTrue($spec->isSatisfiedBy($scheduledContent));

        $scheduledPastContent = $this->createContent()
            ->setStatus('scheduled')
            ->setPublishedAt(new DateTimeImmutable('-1 hour'));
        $this->assertFalse($spec->isSatisfiedBy($scheduledPastContent));

        $publishedContent = $this->createContent()
            ->setStatus('published')
            ->setPublishedAt(new DateTimeImmutable('+1 hour'));
        $this->assertFalse($spec->isSatisfiedBy($publishedContent));
    }

    public function testIsArchivedReturnsTrueOnlyWhenStatusIsArchived(): void
    {
        $spec = new IsArchived();

        $archivedContent = $this->createContent()->setStatus('archived');
        $this->assertTrue($spec->isSatisfiedBy($archivedContent));

        $draftContent = $this->createContent()->setStatus('draft');
        $this->assertFalse($spec->isSatisfiedBy($draftContent));

        $publishedContent = $this->createContent()->setStatus('published');
        $this->assertFalse($spec->isSatisfiedBy($publishedContent));
    }

    public function testSpecificationCompositionReturnsFalseForImpossibleCombination(): void
    {
        $content = $this->createContent()
            ->setStatus('published')
            ->setPublishedAt(new DateTimeImmutable('-1 hour'));

        $spec = (new IsPublished())->and(new IsDraft());

        $this->assertFalse($spec->isSatisfiedBy($content));
    }

    private function createContent(): ContentInterface
    {
        return new class implements ContentInterface {
            use ContentTrait;
        };
    }
}
