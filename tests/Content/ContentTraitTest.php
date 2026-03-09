<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Content;

use Gedmo\Mapping\Annotation as Gedmo;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Content\ContentInterface;
use PsychedCms\Core\Content\ContentTrait;
use ReflectionClass;
use Symfony\Component\Validator\Constraints as Assert;

final class ContentTraitTest extends TestCase
{
    public function testTraitProvidesPropertiesWithCorrectDefaultValues(): void
    {
        $entity = $this->createTestEntity();

        $this->assertNull($entity->getId());
        $this->assertNull($entity->getSlug());
        $this->assertNull($entity->getCreatedAt());
        $this->assertNull($entity->getUpdatedAt());
    }

    public function testSetSlugReturnsStaticForFluentInterface(): void
    {
        $entity = $this->createTestEntity();

        $result = $entity->setSlug('test-slug');
        $this->assertSame($entity, $result);
        $this->assertSame('test-slug', $entity->getSlug());
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

    private function createTestEntity(): ContentInterface
    {
        return new class implements ContentInterface {
            use ContentTrait;

            public function getAuthor(): ?object
            {
                return null;
            }
        };
    }
}
