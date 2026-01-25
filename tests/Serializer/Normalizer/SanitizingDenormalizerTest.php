<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Serializer\Normalizer;

use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttribute;
use PsychedCms\Core\Serializer\Normalizer\SanitizingDenormalizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class SanitizingDenormalizerTest extends TestCase
{
    public function testHtmlIsSanitizedWhenSanitiseAndAllowHtmlAreTrue(): void
    {
        $sanitizer = $this->createMock(HtmlSanitizerInterface::class);
        $sanitizer->expects($this->once())
            ->method('sanitize')
            ->with('<p>Hello</p><script>alert("xss")</script>')
            ->willReturn('<p>Hello</p>');

        $innerDenormalizer = $this->createMock(DenormalizerInterface::class);
        $innerDenormalizer->method('denormalize')
            ->willReturnCallback(function ($data, $type) {
                $entity = new EntityWithHtmlField();
                $entity->content = $data['content'];
                return $entity;
            });

        $denormalizer = new SanitizingDenormalizer($sanitizer);
        $denormalizer->setDenormalizer($innerDenormalizer);

        $result = $denormalizer->denormalize(
            ['content' => '<p>Hello</p><script>alert("xss")</script>'],
            EntityWithHtmlField::class
        );

        $this->assertSame('<p>Hello</p>', $result->content);
    }

    public function testTagsAreStrippedWhenSanitiseTrueAndAllowHtmlFalse(): void
    {
        $sanitizer = $this->createMock(HtmlSanitizerInterface::class);

        $innerDenormalizer = $this->createMock(DenormalizerInterface::class);
        $innerDenormalizer->method('denormalize')
            ->willReturnCallback(function ($data, $type) {
                $entity = new EntityWithPlainTextField();
                $entity->title = $data['title'];
                return $entity;
            });

        $denormalizer = new SanitizingDenormalizer($sanitizer);
        $denormalizer->setDenormalizer($innerDenormalizer);

        $result = $denormalizer->denormalize(
            ['title' => '<b>Bold</b> and <i>italic</i>'],
            EntityWithPlainTextField::class
        );

        $this->assertSame('Bold and italic', $result->title);
    }

    public function testContentIsUnchangedWhenSanitiseIsFalse(): void
    {
        $sanitizer = $this->createMock(HtmlSanitizerInterface::class);
        $sanitizer->expects($this->never())->method('sanitize');

        $innerDenormalizer = $this->createMock(DenormalizerInterface::class);
        $innerDenormalizer->method('denormalize')
            ->willReturnCallback(function ($data, $type) {
                $entity = new EntityWithUnsanitizedField();
                $entity->rawHtml = $data['rawHtml'];
                return $entity;
            });

        $denormalizer = new SanitizingDenormalizer($sanitizer);
        $denormalizer->setDenormalizer($innerDenormalizer);

        $result = $denormalizer->denormalize(
            ['rawHtml' => '<script>alert("trusted")</script>'],
            EntityWithUnsanitizedField::class
        );

        $this->assertSame('<script>alert("trusted")</script>', $result->rawHtml);
    }

    public function testNonStringValuesArePassedThroughUnchanged(): void
    {
        $sanitizer = $this->createMock(HtmlSanitizerInterface::class);
        $sanitizer->expects($this->never())->method('sanitize');

        $innerDenormalizer = $this->createMock(DenormalizerInterface::class);
        $innerDenormalizer->method('denormalize')
            ->willReturnCallback(function ($data, $type) {
                $entity = new EntityWithMixedTypes();
                $entity->count = $data['count'];
                $entity->active = $data['active'];
                return $entity;
            });

        $denormalizer = new SanitizingDenormalizer($sanitizer);
        $denormalizer->setDenormalizer($innerDenormalizer);

        $result = $denormalizer->denormalize(
            ['count' => 42, 'active' => true],
            EntityWithMixedTypes::class
        );

        $this->assertSame(42, $result->count);
        $this->assertTrue($result->active);
    }

    public function testAlreadyCalledContextPreventsInfiniteRecursion(): void
    {
        $sanitizer = $this->createMock(HtmlSanitizerInterface::class);

        $denormalizer = new SanitizingDenormalizer($sanitizer);

        $context = [SanitizingDenormalizer::ALREADY_CALLED => true];

        $this->assertFalse(
            $denormalizer->supportsDenormalization(['title' => 'test'], EntityWithHtmlField::class, null, $context)
        );
    }
}

class EntityWithHtmlField
{
    #[FieldAttribute(sanitise: true, allowHtml: true)]
    public string $content;
}

class EntityWithPlainTextField
{
    #[FieldAttribute(sanitise: true, allowHtml: false)]
    public string $title;
}

class EntityWithUnsanitizedField
{
    #[FieldAttribute(sanitise: false)]
    public string $rawHtml;
}

class EntityWithMixedTypes
{
    #[FieldAttribute]
    public int $count;

    #[FieldAttribute]
    public bool $active;
}
