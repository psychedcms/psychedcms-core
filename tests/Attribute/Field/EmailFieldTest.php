<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\EmailField;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use ReflectionClass;

final class EmailFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new EmailField();

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsEmail(): void
    {
        $attribute = new EmailField();

        $this->assertSame('email', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectType(): void
    {
        $attribute = new EmailField();
        $schema = $attribute->toSchemaArray();

        $this->assertSame('email', $schema['type']);
    }

    public function testInheritsAllBaseOptions(): void
    {
        $attribute = new EmailField(
            label: 'Email Address',
            group: 'contact',
            placeholder: 'user@example.com',
            info: 'Enter your email',
            required: true,
        );

        $schema = $attribute->toSchemaArray();

        $this->assertSame('Email Address', $schema['label']);
        $this->assertSame('contact', $schema['group']);
        $this->assertSame('user@example.com', $schema['placeholder']);
        $this->assertSame('Enter your email', $schema['info']);
        $this->assertTrue($schema['required']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(EmailField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
