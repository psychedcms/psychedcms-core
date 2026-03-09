<?php

declare(strict_types=1);

namespace PsychedCms\Core\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ValidCollectionValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidCollection) {
            throw new UnexpectedTypeException($constraint, ValidCollection::class);
        }

        if ($value === null) {
            return;
        }

        if (!is_array($value)) {
            $this->context->buildViolation($constraint->invalidTypeMessage)
                ->addViolation();

            return;
        }

        $count = count($value);

        if ($constraint->min > 0 && $count < $constraint->min) {
            $this->context->buildViolation($constraint->tooFewItemsMessage)
                ->setParameter('{{ min }}', (string) $constraint->min)
                ->addViolation();
        }

        if ($constraint->max > 0 && $count > $constraint->max) {
            $this->context->buildViolation($constraint->tooManyItemsMessage)
                ->setParameter('{{ max }}', (string) $constraint->max)
                ->addViolation();
        }

        // Normalize schema keys
        $schemaKeys = array_keys($constraint->schema);
        $normalizedSchema = [];
        foreach ($constraint->schema as $key => $fieldDef) {
            if (is_string($fieldDef)) {
                $normalizedSchema[$key] = ['type' => $fieldDef];
            } else {
                $normalizedSchema[$key] = $fieldDef;
            }
        }

        foreach ($value as $index => $item) {
            if (!is_array($item)) {
                $this->context->buildViolation($constraint->invalidItemMessage)
                    ->setParameter('{{ index }}', (string) ($index + 1))
                    ->addViolation();

                continue;
            }

            $itemKeys = array_keys($item);

            // Check for unexpected keys
            foreach ($itemKeys as $key) {
                if (!in_array($key, $schemaKeys, true)) {
                    $this->context->buildViolation($constraint->unexpectedKeyMessage)
                        ->setParameter('{{ index }}', (string) ($index + 1))
                        ->setParameter('{{ key }}', $key)
                        ->addViolation();
                }
            }

            // Check for missing keys
            foreach ($schemaKeys as $key) {
                if (!array_key_exists($key, $item)) {
                    $this->context->buildViolation($constraint->missingKeyMessage)
                        ->setParameter('{{ index }}', (string) ($index + 1))
                        ->setParameter('{{ key }}', $key)
                        ->addViolation();
                }
            }

            // Validate sub-field types
            foreach ($normalizedSchema as $key => $fieldDef) {
                if (!array_key_exists($key, $item)) {
                    continue;
                }

                $fieldValue = $item[$key];
                $fieldType = $fieldDef['type'] ?? 'text';

                if ($fieldValue === null) {
                    continue;
                }

                $this->validateFieldType($constraint, $index, $key, $fieldValue, $fieldType);
            }
        }
    }

    private function validateFieldType(
        ValidCollection $constraint,
        int $index,
        string $key,
        mixed $value,
        string $type,
    ): void {
        $valid = match ($type) {
            'text', 'email', 'select', 'date' => is_string($value),
            'number' => is_numeric($value),
            'checkbox' => is_bool($value),
            default => true,
        };

        if (!$valid) {
            $expected = match ($type) {
                'text', 'email', 'select', 'date' => 'string',
                'number' => 'numeric',
                'checkbox' => 'boolean',
                default => $type,
            };

            $this->context->buildViolation($constraint->invalidFieldTypeMessage)
                ->setParameter('{{ index }}', (string) ($index + 1))
                ->setParameter('{{ field }}', $key)
                ->setParameter('{{ expected }}', $expected)
                ->setParameter('{{ actual }}', get_debug_type($value))
                ->addViolation();
        }
    }
}
