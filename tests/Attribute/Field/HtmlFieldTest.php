<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use PsychedCms\Core\Attribute\Field\HtmlField;
use ReflectionClass;

final class HtmlFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new HtmlField();

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsHtml(): void
    {
        $attribute = new HtmlField();

        $this->assertSame('html', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectType(): void
    {
        $attribute = new HtmlField();
        $schema = $attribute->toSchemaArray();

        $this->assertSame('html', $schema['type']);
    }

    public function testAllowHtmlDefaultsToTrue(): void
    {
        $attribute = new HtmlField();

        $this->assertTrue($attribute->allowHtml);
    }

    public function testAllowHtmlIncludedInSchema(): void
    {
        $attribute = new HtmlField();
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('allowHtml', $schema);
        $this->assertTrue($schema['allowHtml']);
    }

    public function testHeightIncludedInSchemaWhenSet(): void
    {
        $attribute = new HtmlField(height: 15);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('height', $schema);
        $this->assertSame(15, $schema['height']);
    }

    public function testHeightOmittedFromSchemaWhenNull(): void
    {
        $attribute = new HtmlField();
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('height', $schema);
    }

    public function testAllowHtmlCanBeOverridden(): void
    {
        $attribute = new HtmlField(allowHtml: false);

        $this->assertFalse($attribute->allowHtml);
    }

    public function testInheritsAllBaseOptions(): void
    {
        $attribute = new HtmlField(
            label: 'Content',
            group: 'content',
            required: true,
            height: 20,
        );

        $schema = $attribute->toSchemaArray();

        $this->assertSame('Content', $schema['label']);
        $this->assertSame('content', $schema['group']);
        $this->assertTrue($schema['required']);
        $this->assertSame(20, $schema['height']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(HtmlField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
