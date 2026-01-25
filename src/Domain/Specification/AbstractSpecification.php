<?php

declare(strict_types=1);

namespace PsychedCms\Core\Domain\Specification;

abstract class AbstractSpecification implements SpecificationInterface
{
    abstract public function isSatisfiedBy(object $candidate): bool;

    public function and(SpecificationInterface $specification): SpecificationInterface
    {
        return new AndSpecification($this, $specification);
    }

    public function or(SpecificationInterface $specification): SpecificationInterface
    {
        return new OrSpecification($this, $specification);
    }

    public function not(): SpecificationInterface
    {
        return new NotSpecification($this);
    }
}
