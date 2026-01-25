<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use PsychedCms\Core\Attribute\Field\SlugField;
use ReflectionClass;

final class SlugFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new SlugField();

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsSlug(): void
    {
        $attribute = new SlugField();

        $this->assertSame('slug', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectType(): void
    {
        $attribute = new SlugField();
        $schema = $attribute->toSchemaArray();

        $this->assertSame('slug', $schema['type']);
    }

    public function testUsesAcceptsStringValue(): void
    {
        $attribute = new SlugField(uses: 'title');

        $this->assertSame('title', $attribute->uses);
    }

    public function testUsesAcceptsArrayOfStrings(): void
    {
        $attribute = new SlugField(uses: ['title', 'subtitle']);

        $this->assertSame(['title', 'subtitle'], $attribute->uses);
    }

    public function testUsesIncludedInSchemaWhenString(): void
    {
        $attribute = new SlugField(uses: 'title');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('uses', $schema);
        $this->assertSame('title', $schema['uses']);
    }

    public function testUsesIncludedInSchemaWhenArray(): void
    {
        $attribute = new SlugField(uses: ['title', 'subtitle']);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('uses', $schema);
        $this->assertSame(['title', 'subtitle'], $schema['uses']);
    }

    public function testUsesOmittedFromSchemaWhenNull(): void
    {
        $attribute = new SlugField();
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('uses', $schema);
    }

    public function testAllowNumericDefaultsToFalse(): void
    {
        $attribute = new SlugField();

        $this->assertFalse($attribute->allowNumeric);
    }

    public function testAllowNumericOmittedFromSchemaWhenDefault(): void
    {
        $attribute = new SlugField();
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('allowNumeric', $schema);
    }

    public function testAllowNumericIncludedInSchemaWhenTrue(): void
    {
        $attribute = new SlugField(allowNumeric: true);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('allowNumeric', $schema);
        $this->assertTrue($schema['allowNumeric']);
    }

    public function testAllOptionsIncludedInSchema(): void
    {
        $attribute = new SlugField(
            uses: ['title', 'date'],
            allowNumeric: true,
            label: 'URL Slug',
            required: true,
        );
        $schema = $attribute->toSchemaArray();

        $this->assertSame('slug', $schema['type']);
        $this->assertSame(['title', 'date'], $schema['uses']);
        $this->assertTrue($schema['allowNumeric']);
        $this->assertSame('URL Slug', $schema['label']);
        $this->assertTrue($schema['required']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(SlugField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
