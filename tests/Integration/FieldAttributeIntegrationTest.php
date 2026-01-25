<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Integration;

use ApiPlatform\JsonSchema\Schema;
use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttribute;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use PsychedCms\Core\OpenApi\Factory\FieldAttributeSchemaFactory;
use PsychedCms\Core\Serializer\Normalizer\SanitizingDenormalizer;
use PsychedCms\Core\Validator\Loader\FieldAttributeValidatorLoader;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class FieldAttributeIntegrationTest extends TestCase
{
    public function testSchemaFactoryInjectsFieldMetadataForEntityProperties(): void
    {
        $schema = $this->createSchemaWithDefinitions('ArticleEntity', [
            'title' => ['type' => 'string'],
            'status' => ['type' => 'string'],
            'body' => ['type' => 'string'],
        ]);

        $decorated = $this->createMock(SchemaFactoryInterface::class);
        $decorated->method('buildSchema')->willReturn($schema);

        $factory = new FieldAttributeSchemaFactory($decorated);
        $result = $factory->buildSchema(ArticleEntity::class);

        $definitions = $result->getDefinitions();
        $properties = $definitions['ArticleEntity']['properties'];

        $this->assertArrayHasKey('x-psychedcms', (array) $properties['title']);
        $this->assertSame('Article Title', $properties['title']['x-psychedcms']['label']);
        $this->assertTrue($properties['title']['x-psychedcms']['required']);

        $this->assertArrayHasKey('x-psychedcms', (array) $properties['status']);
        $this->assertSame('draft', $properties['status']['x-psychedcms']['default']);

        $this->assertArrayHasKey('x-psychedcms', (array) $properties['body']);
        $this->assertTrue($properties['body']['x-psychedcms']['searchable']);
    }

    public function testValidatorLoaderGeneratesConstraintsForPatternFields(): void
    {
        $metadata = new ClassMetadata(ArticleEntity::class);
        $loader = new FieldAttributeValidatorLoader();

        $result = $loader->loadClassMetadata($metadata);

        $this->assertTrue($result);

        $slugConstraints = $metadata->getPropertyMetadata('slug');
        $this->assertNotEmpty($slugConstraints);
        $this->assertCount(1, $slugConstraints[0]->getConstraints());
    }

    public function testSanitizingDenormalizerProcessesHtmlContent(): void
    {
        $sanitizer = $this->createMock(HtmlSanitizerInterface::class);
        $sanitizer->method('sanitize')
            ->with('<p>Content</p><script>bad</script>')
            ->willReturn('<p>Content</p>');

        $innerDenormalizer = $this->createMock(DenormalizerInterface::class);
        $innerDenormalizer->method('denormalize')
            ->willReturnCallback(function ($data, $type) {
                $entity = new ArticleEntity();
                $entity->body = $data['body'] ?? null;
                return $entity;
            });

        $denormalizer = new SanitizingDenormalizer($sanitizer);
        $denormalizer->setDenormalizer($innerDenormalizer);

        $result = $denormalizer->denormalize(
            ['body' => '<p>Content</p><script>bad</script>'],
            ArticleEntity::class
        );

        $this->assertSame('<p>Content</p>', $result->body);
    }

    public function testFieldAttributeImplementsInterface(): void
    {
        $attribute = new FieldAttribute(
            label: 'Test',
            default: 'value',
            required: true
        );

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
        $this->assertSame('field', $attribute->getFieldType());

        $schema = $attribute->toSchemaArray();
        $this->assertSame('field', $schema['type']);
        $this->assertSame('Test', $schema['label']);
        $this->assertSame('value', $schema['default']);
        $this->assertTrue($schema['required']);
    }

    public function testMultipleFieldAttributesOnSameEntityWorkTogether(): void
    {
        $schema = $this->createSchemaWithDefinitions('ArticleEntity', [
            'title' => ['type' => 'string'],
            'status' => ['type' => 'string'],
            'body' => ['type' => 'string'],
            'slug' => ['type' => 'string'],
        ]);

        $decorated = $this->createMock(SchemaFactoryInterface::class);
        $decorated->method('buildSchema')->willReturn($schema);

        $factory = new FieldAttributeSchemaFactory($decorated);
        $result = $factory->buildSchema(ArticleEntity::class);

        $definitions = $result->getDefinitions();
        $properties = $definitions['ArticleEntity']['properties'];

        $annotatedCount = 0;
        foreach ($properties as $prop) {
            if (isset($prop['x-psychedcms'])) {
                $annotatedCount++;
            }
        }

        $this->assertSame(4, $annotatedCount);
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

class ArticleEntity
{
    #[FieldAttribute(label: 'Article Title', required: true)]
    public ?string $title = null;

    #[FieldAttribute(default: 'draft')]
    public ?string $status = null;

    #[FieldAttribute(searchable: true, sanitise: true, allowHtml: true)]
    public ?string $body = null;

    #[FieldAttribute(pattern: '/^[a-z0-9-]+$/')]
    public ?string $slug = null;
}
