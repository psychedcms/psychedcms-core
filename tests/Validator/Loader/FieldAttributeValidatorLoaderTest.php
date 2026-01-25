<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Validator\Loader;

use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\EmailField;
use PsychedCms\Core\Attribute\Field\FieldAttribute;
use PsychedCms\Core\Validator\Loader\FieldAttributeValidatorLoader;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class FieldAttributeValidatorLoaderTest extends TestCase
{
    public function testRegexConstraintIsGeneratedWhenPatternIsSet(): void
    {
        $metadata = new ClassMetadata(EntityWithPattern::class);
        $loader = new FieldAttributeValidatorLoader();

        $result = $loader->loadClassMetadata($metadata);

        $this->assertTrue($result);

        $propertyMetadata = $metadata->getPropertyMetadata('code');
        $this->assertNotEmpty($propertyMetadata);

        $constraints = $propertyMetadata[0]->getConstraints();
        $this->assertCount(1, $constraints);
        $this->assertInstanceOf(Regex::class, $constraints[0]);
        $this->assertSame('/^[A-Z]{3}$/', $constraints[0]->pattern);
    }

    public function testNoConstraintIsGeneratedWhenPatternIsNull(): void
    {
        $metadata = new ClassMetadata(EntityWithoutPattern::class);
        $loader = new FieldAttributeValidatorLoader();

        $result = $loader->loadClassMetadata($metadata);

        $this->assertFalse($result);

        $propertyMetadata = $metadata->getPropertyMetadata('title');
        $this->assertEmpty($propertyMetadata);
    }

    public function testErrorMessageIncludesFieldLabelWhenAvailable(): void
    {
        $metadata = new ClassMetadata(EntityWithLabelAndPattern::class);
        $loader = new FieldAttributeValidatorLoader();

        $loader->loadClassMetadata($metadata);

        $propertyMetadata = $metadata->getPropertyMetadata('productCode');
        $constraints = $propertyMetadata[0]->getConstraints();

        $this->assertStringContainsString('Product Code', $constraints[0]->message);
    }

    public function testErrorMessageFallsBackToPropertyNameWhenNoLabel(): void
    {
        $metadata = new ClassMetadata(EntityWithPattern::class);
        $loader = new FieldAttributeValidatorLoader();

        $loader->loadClassMetadata($metadata);

        $propertyMetadata = $metadata->getPropertyMetadata('code');
        $constraints = $propertyMetadata[0]->getConstraints();

        $this->assertStringContainsString('code', $constraints[0]->message);
    }

    public function testLoaderReturnsTrueWhenConstraintsAreAdded(): void
    {
        $metadata = new ClassMetadata(EntityWithPattern::class);
        $loader = new FieldAttributeValidatorLoader();

        $result = $loader->loadClassMetadata($metadata);

        $this->assertTrue($result);
    }

    public function testEmailConstraintIsGeneratedForEmailField(): void
    {
        $metadata = new ClassMetadata(EntityWithEmailField::class);
        $loader = new FieldAttributeValidatorLoader();

        $result = $loader->loadClassMetadata($metadata);

        $this->assertTrue($result);

        $propertyMetadata = $metadata->getPropertyMetadata('email');
        $this->assertNotEmpty($propertyMetadata);

        $constraints = $propertyMetadata[0]->getConstraints();
        $this->assertCount(1, $constraints);
        $this->assertInstanceOf(Email::class, $constraints[0]);
    }

    public function testEmailConstraintIncludesLabelInMessage(): void
    {
        $metadata = new ClassMetadata(EntityWithEmailFieldAndLabel::class);
        $loader = new FieldAttributeValidatorLoader();

        $loader->loadClassMetadata($metadata);

        $propertyMetadata = $metadata->getPropertyMetadata('contactEmail');
        $constraints = $propertyMetadata[0]->getConstraints();

        $this->assertInstanceOf(Email::class, $constraints[0]);
        $this->assertStringContainsString('Contact Email', $constraints[0]->message);
    }

    public function testEmailAndPatternConstraintsCanCoexist(): void
    {
        $metadata = new ClassMetadata(EntityWithEmailFieldAndPattern::class);
        $loader = new FieldAttributeValidatorLoader();

        $result = $loader->loadClassMetadata($metadata);

        $this->assertTrue($result);

        $propertyMetadata = $metadata->getPropertyMetadata('restrictedEmail');
        $constraints = $propertyMetadata[0]->getConstraints();

        $this->assertCount(2, $constraints);

        $constraintTypes = array_map(static fn ($c) => $c::class, $constraints);
        $this->assertContains(Email::class, $constraintTypes);
        $this->assertContains(Regex::class, $constraintTypes);
    }
}

class EntityWithPattern
{
    #[FieldAttribute(pattern: '/^[A-Z]{3}$/')]
    public string $code;
}

class EntityWithoutPattern
{
    #[FieldAttribute(label: 'Title')]
    public string $title;
}

class EntityWithLabelAndPattern
{
    #[FieldAttribute(label: 'Product Code', pattern: '/^[A-Z0-9]+$/')]
    public string $productCode;
}

class EntityWithEmailField
{
    #[EmailField]
    public string $email;
}

class EntityWithEmailFieldAndLabel
{
    #[EmailField(label: 'Contact Email')]
    public string $contactEmail;
}

class EntityWithEmailFieldAndPattern
{
    #[EmailField(pattern: '/^[a-z]+@company\.com$/')]
    public string $restrictedEmail;
}
