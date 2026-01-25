<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Content;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Content\ContentInterface;
use ReflectionClass;

final class ContentInterfaceTest extends TestCase
{
    public function testInterfaceIsInCorrectNamespace(): void
    {
        $reflection = new ReflectionClass(ContentInterface::class);

        $this->assertSame('PsychedCms\Core\Content', $reflection->getNamespaceName());
    }

    public function testInterfaceDefinesAllEightGetterMethods(): void
    {
        $reflection = new ReflectionClass(ContentInterface::class);
        $methods = $reflection->getMethods();
        $methodNames = array_map(fn($method) => $method->getName(), $methods);

        $expectedMethods = [
            'getId',
            'getSlug',
            'getStatus',
            'getCreatedAt',
            'getUpdatedAt',
            'getPublishedAt',
            'getDepublishedAt',
            'getAuthor',
        ];

        foreach ($expectedMethods as $expectedMethod) {
            $this->assertContains($expectedMethod, $methodNames, "Interface must define {$expectedMethod}() method");
        }
    }

    public function testReturnTypesAreNullableWhereAppropriate(): void
    {
        $reflection = new ReflectionClass(ContentInterface::class);

        $nullableMethods = ['getId', 'getSlug', 'getCreatedAt', 'getUpdatedAt', 'getPublishedAt', 'getDepublishedAt', 'getAuthor'];
        foreach ($nullableMethods as $methodName) {
            $method = $reflection->getMethod($methodName);
            $returnType = $method->getReturnType();
            $this->assertTrue($returnType->allowsNull(), "{$methodName}() should return nullable type");
        }

        $nonNullableMethod = $reflection->getMethod('getStatus');
        $this->assertFalse($nonNullableMethod->getReturnType()->allowsNull(), "getStatus() should return non-nullable string");
    }

    public function testMockImplementationSatisfiesInterface(): void
    {
        $mock = new class implements ContentInterface {
            public function getId(): ?int
            {
                return 1;
            }

            public function getSlug(): ?string
            {
                return 'test-slug';
            }

            public function getStatus(): string
            {
                return 'draft';
            }

            public function getCreatedAt(): ?DateTimeImmutable
            {
                return new DateTimeImmutable();
            }

            public function getUpdatedAt(): ?DateTimeImmutable
            {
                return new DateTimeImmutable();
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
        };

        $this->assertInstanceOf(ContentInterface::class, $mock);
        $this->assertSame(1, $mock->getId());
        $this->assertSame('test-slug', $mock->getSlug());
        $this->assertSame('draft', $mock->getStatus());
    }
}
