<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use PsychedCms\Core\Attribute\Field\TextField;
use ReflectionClass;

final class TextFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new TextField();

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsText(): void
    {
        $attribute = new TextField();

        $this->assertSame('text', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectType(): void
    {
        $attribute = new TextField();
        $schema = $attribute->toSchemaArray();

        $this->assertSame('text', $schema['type']);
    }

    public function testInheritsAllBaseOptions(): void
    {
        $attribute = new TextField(
            label: 'Test Label',
            group: 'content',
            placeholder: 'Enter text',
            info: 'Help text',
            required: true,
        );

        $schema = $attribute->toSchemaArray();

        $this->assertSame('Test Label', $schema['label']);
        $this->assertSame('content', $schema['group']);
        $this->assertSame('Enter text', $schema['placeholder']);
        $this->assertSame('Help text', $schema['info']);
        $this->assertTrue($schema['required']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(TextField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
