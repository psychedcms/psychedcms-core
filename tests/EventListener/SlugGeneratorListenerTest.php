<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\EventListener;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\SlugSource;
use PsychedCms\Core\Content\EntityInterface;
use PsychedCms\Core\EventListener\SlugGeneratorListener;
use Symfony\Component\Uid\Ulid;

final class SlugGeneratorListenerTest extends TestCase
{
    private SlugGeneratorListener $listener;

    protected function setUp(): void
    {
        $this->listener = new SlugGeneratorListener();
    }

    public function testGeneratesSlugFromGetTitleWhenNoSlugSourceAttribute(): void
    {
        $entity = new EntityWithTitle();
        $entity->setTitle('My Awesome Festival 2026');

        $this->listener->prePersist($this->makeArgs($entity));

        $this->assertSame('my-awesome-festival-2026', $entity->getSlug());
    }

    public function testUsesSlugSourcePropertyOverGetTitle(): void
    {
        $entity = new EntityWithSlugSourceAndTitle();
        $entity->setTitle('Title Should Be Ignored');
        $entity->setName('Preferred Source Name');

        $this->listener->prePersist($this->makeArgs($entity));

        $this->assertSame('preferred-source-name', $entity->getSlug());
    }

    public function testUsesSlugSourcePropertyWhenNoTitle(): void
    {
        $entity = new EntityWithSlugSourceOnName();
        $entity->setName('Iron Maiden');

        $this->listener->prePersist($this->makeArgs($entity));

        $this->assertSame('iron-maiden', $entity->getSlug());
    }

    public function testSkipsWhenSlugAlreadySet(): void
    {
        $entity = new EntityWithTitle();
        $entity->setTitle('Some Title');
        $entity->setSlug('custom-slug');

        $this->listener->prePersist($this->makeArgs($entity));

        $this->assertSame('custom-slug', $entity->getSlug());
    }

    public function testSkipsWhenSourceIsEmpty(): void
    {
        $entity = new EntityWithTitle();
        $entity->setTitle('');

        $this->listener->prePersist($this->makeArgs($entity));

        $this->assertNull($entity->getSlug());
    }

    public function testSkipsWhenEntityHasNoSourceFieldAtAll(): void
    {
        $entity = new EntityWithoutSource();

        $this->listener->prePersist($this->makeArgs($entity));

        $this->assertNull($entity->getSlug());
    }

    public function testSkipsNonEntityInterfaceObjects(): void
    {
        $entity = new \stdClass();

        // Should not throw — listener returns early.
        $this->listener->prePersist($this->makeArgs($entity));

        $this->assertFalse(property_exists($entity, 'slug'));
    }

    public function testSpecialCharactersAreSluggedToAscii(): void
    {
        $entity = new EntityWithTitle();
        $entity->setTitle('Café del Münd — Édition Spéciale');

        $this->listener->prePersist($this->makeArgs($entity));

        $this->assertSame('cafe-del-mund-edition-speciale', $entity->getSlug());
    }

    private function makeArgs(object $entity): PrePersistEventArgs
    {
        $em = $this->createMock(EntityManagerInterface::class);
        return new PrePersistEventArgs($entity, $em);
    }
}

abstract class AbstractTestEntity implements EntityInterface
{
    protected ?string $slug = null;

    public function getId(): ?Ulid
    {
        return null;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return null;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return null;
    }
}

class EntityWithTitle extends AbstractTestEntity
{
    private string $title = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }
}

class EntityWithSlugSourceOnName extends AbstractTestEntity
{
    #[SlugSource]
    private string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }
}

class EntityWithSlugSourceAndTitle extends AbstractTestEntity
{
    #[SlugSource]
    private string $name = '';

    private string $title = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }
}

class EntityWithoutSource extends AbstractTestEntity
{
}
