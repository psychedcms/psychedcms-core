<?php

declare(strict_types=1);

namespace PsychedCms\Core\Attribute;

use Attribute;

/**
 * Marks a property as the source from which a content entity's slug
 * is auto-generated when not provided explicitly.
 *
 * Resolution order in SlugGeneratorListener:
 *   1. Property annotated with #[SlugSource] on the entity (or any parent class / trait)
 *   2. Fallback to getTitle() if the method exists
 *   3. Otherwise no slug is generated
 *
 * Typical usage on entities whose primary human-readable field is not "title":
 *
 *     #[SlugSource]
 *     private string $name = '';
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class SlugSource
{
}
