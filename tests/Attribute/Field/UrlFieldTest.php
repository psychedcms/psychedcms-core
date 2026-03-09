<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use PsychedCms\Core\Attribute\Field\UrlField;
use ReflectionClass;

final class UrlFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new UrlField();

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsUrl(): void
    {
        $attribute = new UrlField();

        $this->assertSame('url', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectType(): void
    {
        $attribute = new UrlField();
        $schema = $attribute->toSchemaArray();

        $this->assertSame('url', $schema['type']);
    }

    public function testInheritsAllBaseOptions(): void
    {
        $attribute = new UrlField(
            label: 'Website',
            group: 'links',
            placeholder: 'https://example.com',
            info: 'Enter a URL',
            required: true,
        );

        $schema = $attribute->toSchemaArray();

        $this->assertSame('Website', $schema['label']);
        $this->assertSame('links', $schema['group']);
        $this->assertSame('https://example.com', $schema['placeholder']);
        $this->assertSame('Enter a URL', $schema['info']);
        $this->assertTrue($schema['required']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(UrlField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
