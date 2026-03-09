<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\CollectionField;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use ReflectionClass;

final class CollectionFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new CollectionField(schema: ['name' => 'text']);

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsCollection(): void
    {
        $attribute = new CollectionField(schema: ['name' => 'text']);

        $this->assertSame('collection', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectType(): void
    {
        $attribute = new CollectionField(schema: ['name' => 'text']);
        $schema = $attribute->toSchemaArray();

        $this->assertSame('collection', $schema['type']);
    }

    public function testToSchemaArrayNormalizesStringSchemaValues(): void
    {
        $attribute = new CollectionField(schema: ['platform' => 'select', 'url' => 'text']);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('schema', $schema);
        $this->assertSame(['type' => 'select'], $schema['schema']['platform']);
        $this->assertSame(['type' => 'text'], $schema['schema']['url']);
    }

    public function testToSchemaArrayPreservesRichSchemaValues(): void
    {
        $attribute = new CollectionField(schema: [
            'platform' => ['type' => 'select', 'values' => ['spotify', 'bandcamp']],
            'url' => 'text',
        ]);
        $schema = $attribute->toSchemaArray();

        $this->assertSame(['type' => 'select', 'values' => ['spotify', 'bandcamp']], $schema['schema']['platform']);
        $this->assertSame(['type' => 'text'], $schema['schema']['url']);
    }

    public function testMinDefaultsToZero(): void
    {
        $attribute = new CollectionField(schema: ['name' => 'text']);

        $this->assertSame(0, $attribute->min);
    }

    public function testMinOmittedFromSchemaWhenZero(): void
    {
        $attribute = new CollectionField(schema: ['name' => 'text']);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('min', $schema);
    }

    public function testMinIncludedInSchemaWhenPositive(): void
    {
        $attribute = new CollectionField(schema: ['name' => 'text'], min: 1);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('min', $schema);
        $this->assertSame(1, $schema['min']);
    }

    public function testMaxDefaultsToZero(): void
    {
        $attribute = new CollectionField(schema: ['name' => 'text']);

        $this->assertSame(0, $attribute->max);
    }

    public function testMaxOmittedFromSchemaWhenZero(): void
    {
        $attribute = new CollectionField(schema: ['name' => 'text']);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('max', $schema);
    }

    public function testMaxIncludedInSchemaWhenPositive(): void
    {
        $attribute = new CollectionField(schema: ['name' => 'text'], max: 10);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('max', $schema);
        $this->assertSame(10, $schema['max']);
    }

    public function testSortableDefaultsToTrue(): void
    {
        $attribute = new CollectionField(schema: ['name' => 'text']);

        $this->assertTrue($attribute->sortable);
    }

    public function testSortableOmittedFromSchemaWhenTrue(): void
    {
        $attribute = new CollectionField(schema: ['name' => 'text']);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('sortable', $schema);
    }

    public function testSortableIncludedInSchemaWhenFalse(): void
    {
        $attribute = new CollectionField(schema: ['name' => 'text'], sortable: false);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('sortable', $schema);
        $this->assertFalse($schema['sortable']);
    }

    public function testParentPropertiesPassThrough(): void
    {
        $attribute = new CollectionField(
            schema: ['name' => 'text'],
            label: 'My Collection',
            group: 'content',
            required: true,
        );
        $schema = $attribute->toSchemaArray();

        $this->assertSame('My Collection', $schema['label']);
        $this->assertSame('content', $schema['group']);
        $this->assertTrue($schema['required']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(CollectionField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
