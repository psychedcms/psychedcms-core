<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Content;

use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Content\UserInterface;
use ReflectionClass;

final class UserInterfaceTest extends TestCase
{
    public function testInterfaceIsInCorrectNamespace(): void
    {
        $reflection = new ReflectionClass(UserInterface::class);

        $this->assertSame('PsychedCms\Core\Content', $reflection->getNamespaceName());
    }

    public function testInterfaceDefinesAllGetterMethods(): void
    {
        $reflection = new ReflectionClass(UserInterface::class);
        $methods = $reflection->getMethods();
        $methodNames = array_map(fn($method) => $method->getName(), $methods);

        $expectedMethods = [
            'getEmail',
            'getDisplayName',
            'getRoles',
            'getLocale',
        ];

        foreach ($expectedMethods as $expectedMethod) {
            $this->assertContains($expectedMethod, $methodNames, "Interface must define {$expectedMethod}() method");
        }
    }

    public function testReturnTypesAreNullableWhereAppropriate(): void
    {
        $reflection = new ReflectionClass(UserInterface::class);

        $nullableMethods = ['getEmail', 'getDisplayName', 'getLocale'];
        foreach ($nullableMethods as $methodName) {
            $method = $reflection->getMethod($methodName);
            $returnType = $method->getReturnType();
            $this->assertTrue($returnType->allowsNull(), "{$methodName}() should return nullable type");
        }
    }

    public function testGetRolesReturnsArray(): void
    {
        $reflection = new ReflectionClass(UserInterface::class);
        $method = $reflection->getMethod('getRoles');
        $returnType = $method->getReturnType();

        $this->assertSame('array', $returnType->getName());
        $this->assertFalse($returnType->allowsNull());
    }

    public function testMockImplementationSatisfiesInterface(): void
    {
        $mock = new class implements UserInterface {
            public function getEmail(): ?string
            {
                return 'test@example.com';
            }

            public function getDisplayName(): ?string
            {
                return 'Test User';
            }

            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }

            public function getLocale(): ?string
            {
                return 'en';
            }
        };

        $this->assertInstanceOf(UserInterface::class, $mock);
        $this->assertSame('test@example.com', $mock->getEmail());
        $this->assertSame('Test User', $mock->getDisplayName());
        $this->assertSame(['ROLE_USER'], $mock->getRoles());
        $this->assertSame('en', $mock->getLocale());
    }
}
