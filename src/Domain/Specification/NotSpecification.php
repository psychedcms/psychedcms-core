<?php

declare(strict_types=1);

namespace PsychedCms\Core\Domain\Specification;

final class NotSpecification extends AbstractSpecification
{
    public function __construct(
        private readonly SpecificationInterface $specification,
    ) {
    }

    public function isSatisfiedBy(object $candidate): bool
    {
        return !$this->specification->isSatisfiedBy($candidate);
    }
}
