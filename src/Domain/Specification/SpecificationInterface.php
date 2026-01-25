<?php

declare(strict_types=1);

namespace PsychedCms\Core\Domain\Specification;

interface SpecificationInterface
{
    public function isSatisfiedBy(object $candidate): bool;
}
