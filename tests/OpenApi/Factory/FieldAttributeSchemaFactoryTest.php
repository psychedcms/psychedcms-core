<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\OpenApi\Factory;

use ApiPlatform\JsonSchema\Schema;
use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\ContentType;
use PsychedCms\Core\Attribute\Field\FieldAttribute;
use PsychedCms\Core\Content\ContentInterface;
use PsychedCms\Core\OpenApi\Factory\FieldAttributeSchemaFactory;
use Symfony\Component\Uid\Ulid;

final class FieldAttributeSchemaFactoryTest extends TestCase
{
    public function testXPsychedcmsExtensionIsInjectedIntoPropertySchema(): void
    {
        $schema = $this->createSchemaWithDefinitions('TestEntity', [
            'title' => ['type' => 'string'],
        ]);

        $decorated = $this->createMock(SchemaFactoryInterface::class);
        $decorated->method('buildSchema')->willReturn($schema);

        $factory = new FieldAttributeSchemaFactory($decorated);
        $result = $factory->buildSchema(TestEntityWithFieldAttribute::class);

        $definitions = $result->getDefinitions();
        $titleProperty = $definitions['TestEntity']['properties']['title'] ?? null;

        $this->assertNotNull($titleProperty);
        $this->assertArrayHasKey('x-psychedcms', (array) $titleProperty);
        $this->assertSame('Test Title', $titleProperty['x-psychedcms']['label']);
        $this->assertSame('field', $titleProperty['x-psychedcms']['type']);
    }

    public function testPropertiesWithoutFieldAttributesAreUnchanged(): void
    {
        $schema = $this->createSchemaWithDefinitions('TestEntity', [
            'noAttribute' => ['type' => 'string'],
        ]);

        $decorated = $this->createMock(SchemaFactoryInterface::class);
        $decorated->method('buildSchema')->willReturn($schema);

        $factory = new FieldAttributeSchemaFactory($decorated);
        $result = $factory->buildSchema(TestEntityWithoutFieldAttribute::class);

        $definitions = $result->getDefinitions();
        $noAttributeProperty = $definitions['TestEntity']['properties']['noAttribute'] ?? null;

        $this->assertNotNull($noAttributeProperty);
        $this->assertArrayNotHasKey('x-psychedcms', (array) $noAttributeProperty);
    }

    public function testDecoratorProperlyChainesToInnerFactory(): void
    {
        $schema = new Schema();
        $schema['$ref'] = '#/definitions/TestEntity';

        $decorated = $this->createMock(SchemaFactoryInterface::class);
        $decorated->expects($this->once())
            ->method('buildSchema')
            ->with(
                'TestClass',
                'json',
                Schema::TYPE_OUTPUT,
                null,
                null,
                null,
                false
            )
            ->willReturn($schema);

        $factory = new FieldAttributeSchemaFactory($decorated);
        $result = $factory->buildSchema('TestClass', 'json', Schema::TYPE_OUTPUT, null, null, null, false);

        $this->assertSame($schema, $result);
    }

    public function testReflectionFindsAttributesImplementingFieldAttributeInterface(): void
    {
        $schema = $this->createSchemaWithDefinitions('TestEntity', [
            'body' => ['type' => 'string'],
        ]);

        $decorated = $this->createMock(SchemaFactoryInterface::class);
        $decorated->method('buildSchema')->willReturn($schema);

        $factory = new FieldAttributeSchemaFactory($decorated);
        $result = $factory->buildSchema(TestEntityWithFieldAttribute::class);

        $definitions = $result->getDefinitions();
        $bodyProperty = $definitions['TestEntity']['properties']['body'] ?? null;

        $this->assertNotNull($bodyProperty);
        $this->assertArrayHasKey('x-psychedcms', (array) $bodyProperty);
        $this->assertTrue($bodyProperty['x-psychedcms']['required']);
    }

    public function testNullValuesAreFilteredFromSchemaOutput(): void
    {
        $schema = $this->createSchemaWithDefinitions('TestEntity', [
            'title' => ['type' => 'string'],
        ]);

        $decorated = $this->createMock(SchemaFactoryInterface::class);
        $decorated->method('buildSchema')->willReturn($schema);

        $factory = new FieldAttributeSchemaFactory($decorated);
        $result = $factory->buildSchema(TestEntityWithFieldAttribute::class);

        $definitions = $result->getDefinitions();
        $titleProperty = $definitions['TestEntity']['properties']['title'] ?? null;

        $this->assertNotNull($titleProperty);
        $xPsychedcms = $titleProperty['x-psychedcms'];
        $this->assertArrayNotHasKey('placeholder', $xPsychedcms);
        $this->assertArrayNotHasKey('info', $xPsychedcms);
        $this->assertArrayNotHasKey('group', $xPsychedcms);
    }

    public function testContentTypeAttributeAddsXPsychedcmsAtSchemaRoot(): void
    {
        $schema = $this->createSchemaWithDefinitions('TestContentTypeEntity', [
            'title' => ['type' => 'string'],
        ]);

        $decorated = $this->createMock(SchemaFactoryInterface::class);
        $decorated->method('buildSchema')->willReturn($schema);

        $factory = new FieldAttributeSchemaFactory($decorated);
        $result = $factory->buildSchema(TestContentTypeEntity::class);

        $definitions = $result->getDefinitions();
        $definition = $definitions['TestContentTypeEntity'] ?? null;

        $this->assertNotNull($definition);
        $this->assertArrayHasKey('x-psychedcms', (array) $definition);

        $xPsychedcms = $definition['x-psychedcms'];
        $this->assertSame('TestContentTypeEntities', $xPsychedcms['name']);
        $this->assertSame('TestContentTypeEntity', $xPsychedcms['singularName']);
        $this->assertSame('test-content-type-entities', $xPsychedcms['slug']);
        $this->assertSame('test-content-type-entity', $xPsychedcms['singularSlug']);
        $this->assertSame('fa-file', $xPsychedcms['icon']);
        $this->assertTrue($xPsychedcms['showOnDashboard']);
    }

    public function testNoXPsychedcmsAtRootForClassWithoutContentTypeAttribute(): void
    {
        $schema = $this->createSchemaWithDefinitions('TestEntity', [
            'title' => ['type' => 'string'],
        ]);

        $decorated = $this->createMock(SchemaFactoryInterface::class);
        $decorated->method('buildSchema')->willReturn($schema);

        $factory = new FieldAttributeSchemaFactory($decorated);
        $result = $factory->buildSchema(TestEntityWithFieldAttribute::class);

        $definitions = $result->getDefinitions();
        $definition = $definitions['TestEntity'] ?? null;

        $this->assertNotNull($definition);
        $this->assertArrayNotHasKey('x-psychedcms', (array) $definition);
    }

    public function testNoXPsychedcmsAtRootForContentTypeClassNotImplementingContentInterface(): void
    {
        $schema = $this->createSchemaWithDefinitions('TestContentTypeWithoutInterface', [
            'title' => ['type' => 'string'],
        ]);

        $decorated = $this->createMock(SchemaFactoryInterface::class);
        $decorated->method('buildSchema')->willReturn($schema);

        $factory = new FieldAttributeSchemaFactory($decorated);
        $result = $factory->buildSchema(TestContentTypeWithoutInterface::class);

        $definitions = $result->getDefinitions();
        $definition = $definitions['TestContentTypeWithoutInterface'] ?? null;

        $this->assertNotNull($definition);
        $this->assertArrayNotHasKey('x-psychedcms', (array) $definition);
    }

    public function testBothRootLevelAndPropertyLevelXPsychedcmsWorkTogether(): void
    {
        $schema = $this->createSchemaWithDefinitions('TestContentTypeWithField', [
            'title' => ['type' => 'string'],
        ]);

        $decorated = $this->createMock(SchemaFactoryInterface::class);
        $decorated->method('buildSchema')->willReturn($schema);

        $factory = new FieldAttributeSchemaFactory($decorated);
        $result = $factory->buildSchema(TestContentTypeWithField::class);

        $definitions = $result->getDefinitions();
        $definition = $definitions['TestContentTypeWithField'] ?? null;

        $this->assertNotNull($definition);

        // Check root-level x-psychedcms exists
        $this->assertArrayHasKey('x-psychedcms', (array) $definition);
        $this->assertSame('TestContentTypeWithFields', $definition['x-psychedcms']['name']);

        // Check property-level x-psychedcms exists
        $titleProperty = $definition['properties']['title'] ?? null;
        $this->assertNotNull($titleProperty);
        $this->assertArrayHasKey('x-psychedcms', (array) $titleProperty);
        $this->assertSame('Article Title', $titleProperty['x-psychedcms']['label']);
    }

    private function createSchemaWithDefinitions(string $definitionName, array $properties): Schema
    {
        $schema = new Schema();
        $schema['$ref'] = '#/definitions/' . $definitionName;

        $definitions = $schema->getDefinitions();
        $definition = new \ArrayObject([
            'type' => 'object',
            'properties' => [],
        ]);

        foreach ($properties as $name => $propertySchema) {
            $definition['properties'][$name] = new \ArrayObject($propertySchema);
        }

        $definitions[$definitionName] = $definition;

        return $schema;
    }
}

class TestEntityWithFieldAttribute
{
    #[FieldAttribute(label: 'Test Title')]
    public string $title;

    #[FieldAttribute(required: true, pattern: '/^[a-z]+$/')]
    public string $body;
}

class TestEntityWithoutFieldAttribute
{
    public string $noAttribute;
}

#[ContentType(icon: 'fa-file')]
class TestContentTypeEntity implements ContentInterface
{
    public string $title;

    public function getId(): ?Ulid
    {
        return null;
    }

    public function getSlug(): ?string
    {
        return null;
    }

    public function getStatus(): string
    {
        return 'draft';
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return null;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return null;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return null;
    }

    public function getDepublishedAt(): ?DateTimeImmutable
    {
        return null;
    }

    public function getAuthor(): ?object
    {
        return null;
    }
}

#[ContentType(icon: 'fa-warning')]
class TestContentTypeWithoutInterface
{
    public string $title;
}

#[ContentType]
class TestContentTypeWithField implements ContentInterface
{
    #[FieldAttribute(label: 'Article Title')]
    public string $title;

    public function getId(): ?Ulid
    {
        return null;
    }

    public function getSlug(): ?string
    {
        return null;
    }

    public function getStatus(): string
    {
        return 'draft';
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return null;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return null;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return null;
    }

    public function getDepublishedAt(): ?DateTimeImmutable
    {
        return null;
    }

    public function getAuthor(): ?object
    {
        return null;
    }
}
