<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\OpenApi\Factory;

use ApiPlatform\JsonSchema\Schema;
use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttribute;
use PsychedCms\Core\OpenApi\Factory\FieldAttributeSchemaFactory;

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
