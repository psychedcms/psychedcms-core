<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\DateField;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use ReflectionClass;

final class DateFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new DateField();

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsDate(): void
    {
        $attribute = new DateField();

        $this->assertSame('date', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectType(): void
    {
        $attribute = new DateField();
        $schema = $attribute->toSchemaArray();

        $this->assertSame('date', $schema['type']);
    }

    public function testModeDefaultsToDate(): void
    {
        $attribute = new DateField();

        $this->assertSame('date', $attribute->mode);
    }

    public function testModeOmittedFromSchemaWhenDefault(): void
    {
        $attribute = new DateField();
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('mode', $schema);
    }

    public function testModeIncludedInSchemaWhenDatetime(): void
    {
        $attribute = new DateField(mode: 'datetime');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('mode', $schema);
        $this->assertSame('datetime', $schema['mode']);
    }

    public function testInheritsAllBaseOptions(): void
    {
        $attribute = new DateField(
            label: 'Published At',
            group: 'meta',
            required: true,
            mode: 'datetime',
        );

        $schema = $attribute->toSchemaArray();

        $this->assertSame('Published At', $schema['label']);
        $this->assertSame('meta', $schema['group']);
        $this->assertTrue($schema['required']);
        $this->assertSame('datetime', $schema['mode']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(DateField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
