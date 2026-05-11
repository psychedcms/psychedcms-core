<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\SlugSource;
use ReflectionClass;

final class SlugSourceTest extends TestCase
{
    public function testTargetIsProperty(): void
    {
        $reflection = new ReflectionClass(SlugSource::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertNotEmpty($attributes, 'SlugSource must declare #[Attribute]');

        /** @var Attribute $meta */
        $meta = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $meta->flags);
    }

    public function testCanBeInstantiated(): void
    {
        $attribute = new SlugSource();

        $this->assertInstanceOf(SlugSource::class, $attribute);
    }
}
