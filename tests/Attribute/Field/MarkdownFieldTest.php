<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use PsychedCms\Core\Attribute\Field\MarkdownField;
use ReflectionClass;

final class MarkdownFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new MarkdownField();

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsMarkdown(): void
    {
        $attribute = new MarkdownField();

        $this->assertSame('markdown', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectType(): void
    {
        $attribute = new MarkdownField();
        $schema = $attribute->toSchemaArray();

        $this->assertSame('markdown', $schema['type']);
    }

    public function testAllowHtmlDefaultsToFalse(): void
    {
        $attribute = new MarkdownField();

        $this->assertFalse($attribute->allowHtml);
    }

    public function testAllowHtmlIncludedInSchema(): void
    {
        $attribute = new MarkdownField();
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('allowHtml', $schema);
        $this->assertFalse($schema['allowHtml']);
    }

    public function testHeightIncludedInSchemaWhenSet(): void
    {
        $attribute = new MarkdownField(height: 15);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('height', $schema);
        $this->assertSame(15, $schema['height']);
    }

    public function testHeightOmittedFromSchemaWhenNull(): void
    {
        $attribute = new MarkdownField();
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('height', $schema);
    }

    public function testAllowHtmlCanBeOverridden(): void
    {
        $attribute = new MarkdownField(allowHtml: true);

        $this->assertTrue($attribute->allowHtml);
    }

    public function testInheritsAllBaseOptions(): void
    {
        $attribute = new MarkdownField(
            label: 'Documentation',
            group: 'content',
            required: true,
            height: 25,
        );

        $schema = $attribute->toSchemaArray();

        $this->assertSame('Documentation', $schema['label']);
        $this->assertSame('content', $schema['group']);
        $this->assertTrue($schema['required']);
        $this->assertSame(25, $schema['height']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(MarkdownField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
