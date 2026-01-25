<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use PsychedCms\Core\Attribute\Field\HiddenField;
use ReflectionClass;

final class HiddenFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new HiddenField();

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsHidden(): void
    {
        $attribute = new HiddenField();

        $this->assertSame('hidden', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectType(): void
    {
        $attribute = new HiddenField();
        $schema = $attribute->toSchemaArray();

        $this->assertSame('hidden', $schema['type']);
    }

    public function testReadonlyIsForcedToTrue(): void
    {
        $attribute = new HiddenField();

        $this->assertTrue($attribute->readonly);
    }

    public function testReadonlyRemainsTrue(): void
    {
        $attribute = new HiddenField(readonly: false);

        $this->assertTrue($attribute->readonly);
    }

    public function testReadonlyIncludedInSchema(): void
    {
        $attribute = new HiddenField();
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('readonly', $schema);
        $this->assertTrue($schema['readonly']);
    }

    public function testDefaultValuePreservedInSchema(): void
    {
        $attribute = new HiddenField(default: 'secret-value');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('default', $schema);
        $this->assertSame('secret-value', $schema['default']);
    }

    public function testInheritsBaseOptions(): void
    {
        $attribute = new HiddenField(
            label: 'Internal ID',
            group: 'system',
            default: 'uuid-123',
        );

        $schema = $attribute->toSchemaArray();

        $this->assertSame('Internal ID', $schema['label']);
        $this->assertSame('system', $schema['group']);
        $this->assertSame('uuid-123', $schema['default']);
        $this->assertTrue($schema['readonly']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(HiddenField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
