<?php

declare(strict_types=1);

namespace PsychedCms\Core\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use PsychedCms\Core\Attribute\SlugSource;
use PsychedCms\Core\Content\EntityInterface;
use ReflectionClass;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Auto-generates a slug for any EntityInterface if missing on persist.
 *
 * Source resolution:
 *   1. Property annotated with #[SlugSource] on the entity class hierarchy
 *   2. getTitle() if the method exists
 *   3. Otherwise no slug is generated (caller must provide one)
 */
#[AsDoctrineListener(event: Events::prePersist)]
final class SlugGeneratorListener
{
    private readonly AsciiSlugger $slugger;

    public function __construct()
    {
        $this->slugger = new AsciiSlugger();
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof EntityInterface) {
            return;
        }

        if ($entity->getSlug() !== null && $entity->getSlug() !== '') {
            return;
        }

        $source = $this->resolveSource($entity);
        if ($source === null || $source === '') {
            return;
        }

        $slug = $this->slugger->slug($source)->lower()->toString();

        if (\method_exists($entity, 'setSlug')) {
            $entity->setSlug($slug);
        }
    }

    private function resolveSource(object $entity): ?string
    {
        $propertyName = $this->findSlugSourceProperty($entity);

        if ($propertyName !== null) {
            $value = $this->readProperty($entity, $propertyName);
            if (\is_string($value) && $value !== '') {
                return $value;
            }
        }

        if (\method_exists($entity, 'getTitle')) {
            $value = $entity->getTitle();
            if (\is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function findSlugSourceProperty(object $entity): ?string
    {
        $reflection = new ReflectionClass($entity);

        while ($reflection !== false) {
            foreach ($reflection->getProperties() as $property) {
                if (!empty($property->getAttributes(SlugSource::class))) {
                    return $property->getName();
                }
            }
            $reflection = $reflection->getParentClass();
        }

        return null;
    }

    private function readProperty(object $entity, string $property): mixed
    {
        $getter = 'get' . \ucfirst($property);
        if (\method_exists($entity, $getter)) {
            return $entity->{$getter}();
        }

        $reflection = new ReflectionClass($entity);
        $prop = $this->findProperty($reflection, $property);
        if ($prop === null) {
            return null;
        }
        $prop->setAccessible(true);

        return $prop->getValue($entity);
    }

    private function findProperty(ReflectionClass $reflection, string $name): ?\ReflectionProperty
    {
        while ($reflection !== false) {
            if ($reflection->hasProperty($name)) {
                return $reflection->getProperty($name);
            }
            $reflection = $reflection->getParentClass();
        }

        return null;
    }
}
