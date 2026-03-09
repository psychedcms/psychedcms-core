<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Validator\Constraint;

use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Validator\Constraint\ValidCollection;
use PsychedCms\Core\Validator\Constraint\ValidCollectionValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class ValidCollectionValidatorTest extends TestCase
{
    private ValidCollectionValidator $validator;
    private ExecutionContextInterface $context;
    private ConstraintViolationBuilderInterface $violationBuilder;

    protected function setUp(): void
    {
        $this->validator = new ValidCollectionValidator();

        $this->violationBuilder = $this->createStub(ConstraintViolationBuilderInterface::class);
        $this->violationBuilder->method('setParameter')->willReturnSelf();

        $this->context = $this->createMock(ExecutionContextInterface::class);

        $reflection = new \ReflectionClass(ValidCollectionValidator::class);
        $parent = $reflection->getParentClass();
        $property = $parent->getProperty('context');
        $property->setAccessible(true);
        $property->setValue($this->validator, $this->context);
    }

    public function testValidCollectionPasses(): void
    {
        $constraint = new ValidCollection(
            schema: ['platform' => 'text', 'url' => 'text'],
        );

        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate(
            [
                ['platform' => 'spotify', 'url' => 'https://spotify.com'],
                ['platform' => 'bandcamp', 'url' => 'https://bandcamp.com'],
            ],
            $constraint,
        );
    }

    public function testNullPassesValidation(): void
    {
        $constraint = new ValidCollection(
            schema: ['name' => 'text'],
        );

        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate(null, $constraint);
    }

    public function testNonArrayFailsValidation(): void
    {
        $constraint = new ValidCollection(
            schema: ['name' => 'text'],
        );

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->invalidTypeMessage)
            ->willReturn($this->violationBuilder);

        $this->validator->validate('not an array', $constraint);
    }

    public function testMinEnforcement(): void
    {
        $constraint = new ValidCollection(
            schema: ['name' => 'text'],
            min: 2,
        );

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->tooFewItemsMessage)
            ->willReturn($this->violationBuilder);

        $this->validator->validate(
            [['name' => 'only one']],
            $constraint,
        );
    }

    public function testMaxEnforcement(): void
    {
        $constraint = new ValidCollection(
            schema: ['name' => 'text'],
            max: 1,
        );

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->tooManyItemsMessage)
            ->willReturn($this->violationBuilder);

        $this->validator->validate(
            [['name' => 'one'], ['name' => 'two']],
            $constraint,
        );
    }

    public function testUnexpectedKeysRejected(): void
    {
        $constraint = new ValidCollection(
            schema: ['name' => 'text'],
        );

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->unexpectedKeyMessage)
            ->willReturn($this->violationBuilder);

        $this->validator->validate(
            [['name' => 'test', 'extra' => 'bad']],
            $constraint,
        );
    }

    public function testMissingKeysRejected(): void
    {
        $constraint = new ValidCollection(
            schema: ['name' => 'text', 'url' => 'text'],
        );

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->missingKeyMessage)
            ->willReturn($this->violationBuilder);

        $this->validator->validate(
            [['name' => 'test']],
            $constraint,
        );
    }

    public function testWrongSubFieldTypeRejected(): void
    {
        $constraint = new ValidCollection(
            schema: ['name' => 'text', 'count' => 'number'],
        );

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->invalidFieldTypeMessage)
            ->willReturn($this->violationBuilder);

        $this->validator->validate(
            [['name' => 'test', 'count' => 'not a number']],
            $constraint,
        );
    }

    public function testCheckboxFieldTypeValidation(): void
    {
        $constraint = new ValidCollection(
            schema: ['active' => 'checkbox'],
        );

        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate(
            [['active' => true], ['active' => false]],
            $constraint,
        );
    }

    public function testCheckboxRejectsNonBoolean(): void
    {
        $constraint = new ValidCollection(
            schema: ['active' => 'checkbox'],
        );

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->invalidFieldTypeMessage)
            ->willReturn($this->violationBuilder);

        $this->validator->validate(
            [['active' => 'yes']],
            $constraint,
        );
    }

    public function testNullFieldValuePassesValidation(): void
    {
        $constraint = new ValidCollection(
            schema: ['name' => 'text'],
        );

        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate(
            [['name' => null]],
            $constraint,
        );
    }

    public function testRichSchemaFormat(): void
    {
        $constraint = new ValidCollection(
            schema: ['platform' => ['type' => 'select', 'values' => ['a', 'b']]],
        );

        $this->context->expects($this->never())->method('buildViolation');

        $this->validator->validate(
            [['platform' => 'a']],
            $constraint,
        );
    }
}
