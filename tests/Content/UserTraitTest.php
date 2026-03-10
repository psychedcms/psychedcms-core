<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Content;

use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Content\UserInterface;
use PsychedCms\Core\Content\UserTrait;
use ReflectionClass;

final class UserTraitTest extends TestCase
{
    public function testTraitProvidesPropertiesWithCorrectDefaultValues(): void
    {
        $entity = $this->createTestEntity();

        $this->assertNull($entity->getEmail());
        $this->assertNull($entity->getDisplayName());
        $this->assertSame([], $entity->getRoles());
        $this->assertNull($entity->getLocale());
    }

    public function testSetEmailReturnsStaticForFluentInterface(): void
    {
        $entity = $this->createTestEntity();

        $result = $entity->setEmail('user@example.com');
        $this->assertSame($entity, $result);
        $this->assertSame('user@example.com', $entity->getEmail());
    }

    public function testSetRolesReturnsStaticForFluentInterface(): void
    {
        $entity = $this->createTestEntity();

        $result = $entity->setRoles(['ROLE_ADMIN']);
        $this->assertSame($entity, $result);
        $this->assertSame(['ROLE_ADMIN'], $entity->getRoles());
    }

    public function testSetLocaleReturnsStaticForFluentInterface(): void
    {
        $entity = $this->createTestEntity();

        $result = $entity->setLocale('fr');
        $this->assertSame($entity, $result);
        $this->assertSame('fr', $entity->getLocale());
    }

    public function testSetLocaleToNull(): void
    {
        $entity = $this->createTestEntity();
        $entity->setLocale('en');
        $entity->setLocale(null);

        $this->assertNull($entity->getLocale());
    }

    public function testGetDisplayNameReturnsEmail(): void
    {
        $entity = $this->createTestEntity();
        $entity->setEmail('display@example.com');

        $this->assertSame('display@example.com', $entity->getDisplayName());
    }

    public function testEmailPropertyHasOrmAttributes(): void
    {
        $reflection = new ReflectionClass(UserTrait::class);
        $property = $reflection->getProperty('email');
        $attributes = array_map(fn($attr) => $attr->getName(), $property->getAttributes());

        $this->assertContains(ORM\Column::class, $attributes, 'email should have ORM Column attribute');
    }

    public function testRolesPropertyHasOrmAttributes(): void
    {
        $reflection = new ReflectionClass(UserTrait::class);
        $property = $reflection->getProperty('roles');
        $attributes = array_map(fn($attr) => $attr->getName(), $property->getAttributes());

        $this->assertContains(ORM\Column::class, $attributes, 'roles should have ORM Column attribute');
    }

    public function testClassUsingTraitSatisfiesUserInterface(): void
    {
        $entity = $this->createTestEntity();

        $this->assertInstanceOf(UserInterface::class, $entity);
    }

    private function createTestEntity(): UserInterface
    {
        return new class implements UserInterface {
            use UserTrait;
        };
    }
}
