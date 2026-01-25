<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Domain\Specification;

use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Domain\Specification\AbstractSpecification;
use PsychedCms\Core\Domain\Specification\AndSpecification;
use PsychedCms\Core\Domain\Specification\NotSpecification;
use PsychedCms\Core\Domain\Specification\OrSpecification;
use PsychedCms\Core\Domain\Specification\SpecificationInterface;
use ReflectionClass;

final class SpecificationTest extends TestCase
{
    public function testSpecificationInterfaceRequiresIsSatisfiedByMethod(): void
    {
        $reflection = new ReflectionClass(SpecificationInterface::class);
        $method = $reflection->getMethod('isSatisfiedBy');

        $this->assertTrue($method->hasReturnType());
        $this->assertSame('bool', $method->getReturnType()->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertSame('candidate', $parameters[0]->getName());
        $this->assertSame('object', $parameters[0]->getType()->getName());
    }

    public function testAndMethodReturnsAndSpecification(): void
    {
        $spec1 = $this->createAlwaysTrueSpec();
        $spec2 = $this->createAlwaysTrueSpec();

        $result = $spec1->and($spec2);

        $this->assertInstanceOf(AndSpecification::class, $result);
    }

    public function testOrMethodReturnsOrSpecification(): void
    {
        $spec1 = $this->createAlwaysTrueSpec();
        $spec2 = $this->createAlwaysFalseSpec();

        $result = $spec1->or($spec2);

        $this->assertInstanceOf(OrSpecification::class, $result);
    }

    public function testNotMethodReturnsNotSpecification(): void
    {
        $spec = $this->createAlwaysTrueSpec();

        $result = $spec->not();

        $this->assertInstanceOf(NotSpecification::class, $result);
    }

    public function testSpecificationCompositionWithChainedMethods(): void
    {
        $alwaysTrue = $this->createAlwaysTrueSpec();
        $alwaysFalse = $this->createAlwaysFalseSpec();
        $candidate = new \stdClass();

        $andSpec = $alwaysTrue->and($alwaysFalse);
        $this->assertFalse($andSpec->isSatisfiedBy($candidate));

        $orSpec = $alwaysTrue->or($alwaysFalse);
        $this->assertTrue($orSpec->isSatisfiedBy($candidate));

        $notSpec = $alwaysTrue->not();
        $this->assertFalse($notSpec->isSatisfiedBy($candidate));

        $complexSpec = $alwaysTrue->and($alwaysFalse->not());
        $this->assertTrue($complexSpec->isSatisfiedBy($candidate));
    }

    private function createAlwaysTrueSpec(): AbstractSpecification
    {
        return new class extends AbstractSpecification {
            public function isSatisfiedBy(object $candidate): bool
            {
                return true;
            }
        };
    }

    private function createAlwaysFalseSpec(): AbstractSpecification
    {
        return new class extends AbstractSpecification {
            public function isSatisfiedBy(object $candidate): bool
            {
                return false;
            }
        };
    }
}
