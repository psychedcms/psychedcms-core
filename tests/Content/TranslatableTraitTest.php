<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Content;

use Gedmo\Mapping\Annotation as Gedmo;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Content\TranslatableInterface;
use PsychedCms\Core\Content\TranslatableTrait;
use ReflectionClass;

final class TranslatableTraitTest extends TestCase
{
    public function testLocaleDefaultsToNull(): void
    {
        $entity = $this->createEntity();

        $this->assertNull($entity->getLocale());
    }

    public function testSetTranslatableLocaleReturnsStatic(): void
    {
        $entity = $this->createEntity();

        $result = $entity->setTranslatableLocale('fr');

        $this->assertSame($entity, $result);
    }

    public function testSetAndGetLocale(): void
    {
        $entity = $this->createEntity();

        $entity->setTranslatableLocale('fr');

        $this->assertSame('fr', $entity->getLocale());
    }

    public function testSetLocaleToNull(): void
    {
        $entity = $this->createEntity();

        $entity->setTranslatableLocale('fr');
        $entity->setTranslatableLocale(null);

        $this->assertNull($entity->getLocale());
    }

    public function testLocalePropertyHasGedmoLocaleAttribute(): void
    {
        $reflection = new ReflectionClass(TranslatableTrait::class);
        $property = $reflection->getProperty('locale');
        $attributes = array_map(fn ($attr) => $attr->getName(), $property->getAttributes());

        $this->assertContains(Gedmo\Locale::class, $attributes);
    }

    public function testEntityImplementsTranslatableInterface(): void
    {
        $entity = $this->createEntity();

        $this->assertInstanceOf(TranslatableInterface::class, $entity);
    }

    private function createEntity(): TranslatableInterface
    {
        return new class implements TranslatableInterface {
            use TranslatableTrait;
        };
    }
}
