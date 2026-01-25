<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use PsychedCms\Core\Attribute\Field\TextareaField;
use ReflectionClass;

final class TextareaFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new TextareaField();

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsTextarea(): void
    {
        $attribute = new TextareaField();

        $this->assertSame('textarea', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectType(): void
    {
        $attribute = new TextareaField();
        $schema = $attribute->toSchemaArray();

        $this->assertSame('textarea', $schema['type']);
    }

    public function testInheritsAllBaseOptions(): void
    {
        $attribute = new TextareaField(
            label: 'Description',
            group: 'content',
            placeholder: 'Enter description',
            info: 'A longer text field',
            required: true,
        );

        $schema = $attribute->toSchemaArray();

        $this->assertSame('Description', $schema['label']);
        $this->assertSame('content', $schema['group']);
        $this->assertSame('Enter description', $schema['placeholder']);
        $this->assertSame('A longer text field', $schema['info']);
        $this->assertTrue($schema['required']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(TextareaField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
