<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Content;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Content\ContentInterface;
use PsychedCms\Core\Content\ContentTrait;
use ReflectionClass;
use Symfony\Component\Validator\Constraints as Assert;

final class ContentTraitTest extends TestCase
{
    public function testTraitProvidesAllEightPropertiesWithCorrectDefaultValues(): void
    {
        $entity = $this->createTestEntity();

        $this->assertNull($entity->getId());
        $this->assertNull($entity->getSlug());
        $this->assertSame('draft', $entity->getStatus());
        $this->assertNull($entity->getCreatedAt());
        $this->assertNull($entity->getUpdatedAt());
        $this->assertNull($entity->getPublishedAt());
        $this->assertNull($entity->getDepublishedAt());
        $this->assertNull($entity->getAuthor());
    }

    public function testSettersReturnStaticForFluentInterface(): void
    {
        $entity = $this->createTestEntity();

        $result = $entity->setSlug('test-slug');
        $this->assertSame($entity, $result);

        $result = $entity->setStatus('published');
        $this->assertSame($entity, $result);

        $result = $entity->setPublishedAt(new DateTimeImmutable());
        $this->assertSame($entity, $result);

        $result = $entity->setDepublishedAt(new DateTimeImmutable());
        $this->assertSame($entity, $result);

        $result = $entity->setAuthor(new \stdClass());
        $this->assertSame($entity, $result);
    }

    public function testSlugPropertyHasValidationConstraints(): void
    {
        $reflection = new ReflectionClass(ContentTrait::class);
        $property = $reflection->getProperty('slug');
        $attributes = $property->getAttributes();

        $attributeNames = array_map(fn($attr) => $attr->getName(), $attributes);

        $this->assertContains(Assert\NotBlank::class, $attributeNames, 'Slug should have NotBlank constraint');
        $this->assertContains(Assert\Length::class, $attributeNames, 'Slug should have Length constraint');
        $this->assertContains(Assert\Regex::class, $attributeNames, 'Slug should have Regex constraint');
    }

    public function testTimestampPropertiesHaveGedmoAttributes(): void
    {
        $reflection = new ReflectionClass(ContentTrait::class);

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtAttributes = array_map(fn($attr) => $attr->getName(), $createdAtProperty->getAttributes());
        $this->assertContains(Gedmo\Timestampable::class, $createdAtAttributes, 'createdAt should have Timestampable attribute');

        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtAttributes = array_map(fn($attr) => $attr->getName(), $updatedAtProperty->getAttributes());
        $this->assertContains(Gedmo\Timestampable::class, $updatedAtAttributes, 'updatedAt should have Timestampable attribute');
    }

    public function testClassUsingTraitSatisfiesContentInterface(): void
    {
        $entity = $this->createTestEntity();

        $this->assertInstanceOf(ContentInterface::class, $entity);
    }

    public function testStatusDefaultsToDraft(): void
    {
        $entity = $this->createTestEntity();

        $this->assertSame('draft', $entity->getStatus());
    }

    private function createTestEntity(): ContentInterface
    {
        return new class implements ContentInterface {
            use ContentTrait;
        };
    }
}
