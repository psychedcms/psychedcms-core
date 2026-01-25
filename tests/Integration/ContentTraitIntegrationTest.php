<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Integration;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Content\ContentInterface;
use PsychedCms\Core\Content\ContentTrait;
use PsychedCms\Core\Domain\Specification\IsArchived;
use PsychedCms\Core\Domain\Specification\IsDraft;
use PsychedCms\Core\Domain\Specification\IsPublished;
use PsychedCms\Core\Domain\Specification\IsScheduled;

final class ContentTraitIntegrationTest extends TestCase
{
    public function testNewContentDefaultsToDraftStatus(): void
    {
        $content = $this->createContent();

        $this->assertSame('draft', $content->getStatus());
        $this->assertTrue((new IsDraft())->isSatisfiedBy($content));
    }

    public function testIsPublishedWithRealEntityUsingContentTrait(): void
    {
        $content = $this->createContent()
            ->setStatus('published')
            ->setPublishedAt(new DateTimeImmutable('-1 hour'));

        $spec = new IsPublished();

        $this->assertTrue($spec->isSatisfiedBy($content));
    }

    public function testSpecificationCompositionOnContentEntity(): void
    {
        $publishedContent = $this->createContent()
            ->setStatus('published')
            ->setPublishedAt(new DateTimeImmutable('-1 hour'));

        $draftContent = $this->createContent()
            ->setStatus('draft');

        $publishedOrDraft = (new IsPublished())->or(new IsDraft());

        $this->assertTrue($publishedOrDraft->isSatisfiedBy($publishedContent));
        $this->assertTrue($publishedOrDraft->isSatisfiedBy($draftContent));

        $publishedAndDraft = (new IsPublished())->and(new IsDraft());

        $this->assertFalse($publishedAndDraft->isSatisfiedBy($publishedContent));
        $this->assertFalse($publishedAndDraft->isSatisfiedBy($draftContent));
    }

    public function testContentFlowThroughStatuses(): void
    {
        $content = $this->createContent();

        $this->assertTrue((new IsDraft())->isSatisfiedBy($content));
        $this->assertFalse((new IsPublished())->isSatisfiedBy($content));
        $this->assertFalse((new IsScheduled())->isSatisfiedBy($content));
        $this->assertFalse((new IsArchived())->isSatisfiedBy($content));

        $content->setStatus('scheduled')->setPublishedAt(new DateTimeImmutable('+1 day'));

        $this->assertFalse((new IsDraft())->isSatisfiedBy($content));
        $this->assertFalse((new IsPublished())->isSatisfiedBy($content));
        $this->assertTrue((new IsScheduled())->isSatisfiedBy($content));
        $this->assertFalse((new IsArchived())->isSatisfiedBy($content));

        $content->setStatus('published')->setPublishedAt(new DateTimeImmutable('-1 minute'));

        $this->assertFalse((new IsDraft())->isSatisfiedBy($content));
        $this->assertTrue((new IsPublished())->isSatisfiedBy($content));
        $this->assertFalse((new IsScheduled())->isSatisfiedBy($content));
        $this->assertFalse((new IsArchived())->isSatisfiedBy($content));

        $content->setStatus('archived');

        $this->assertFalse((new IsDraft())->isSatisfiedBy($content));
        $this->assertFalse((new IsPublished())->isSatisfiedBy($content));
        $this->assertFalse((new IsScheduled())->isSatisfiedBy($content));
        $this->assertTrue((new IsArchived())->isSatisfiedBy($content));
    }

    private function createContent(): ContentInterface
    {
        return new class implements ContentInterface {
            use ContentTrait;
        };
    }
}
