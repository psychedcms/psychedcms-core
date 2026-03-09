<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Translation;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\TextField;
use PsychedCms\Core\Attribute\Field\HtmlField;
use PsychedCms\Core\Content\TranslatableInterface;
use PsychedCms\Core\Content\TranslatableTrait;
use PsychedCms\Core\Translation\TranslationValidator;

final class TranslationValidatorTest extends TestCase
{
    private TranslationValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new TranslationValidator();
    }

    public function testGetTranslatableFieldsReturnsAnnotatedFields(): void
    {
        $entity = new TestTranslatableEntity();
        $fields = $this->validator->getTranslatableFields($entity);

        $this->assertSame(['title', 'content'], $fields);
    }

    public function testEntityWithNoTranslatableFieldsIsAlwaysComplete(): void
    {
        $entity = new TestNonTranslatableEntity();

        $this->assertTrue($this->validator->hasCompleteTranslation($entity, 'fr', 'en'));
        $this->assertTrue($this->validator->hasCompleteTranslation($entity, 'en', 'en'));
    }

    public function testDefaultLocaleChecksEntityFields(): void
    {
        $entity = new TestTranslatableEntity();
        $entity->title = 'Hello';
        $entity->content = '<p>World</p>';

        $this->assertTrue($this->validator->hasCompleteTranslation($entity, 'en', 'en'));
    }

    public function testDefaultLocaleFailsWithMissingField(): void
    {
        $entity = new TestTranslatableEntity();
        $entity->title = 'Hello';
        $entity->content = null;

        $this->assertFalse($this->validator->hasCompleteTranslation($entity, 'en', 'en'));
    }

    public function testDefaultLocaleFailsWithEmptyStringField(): void
    {
        $entity = new TestTranslatableEntity();
        $entity->title = 'Hello';
        $entity->content = '   ';

        $this->assertFalse($this->validator->hasCompleteTranslation($entity, 'en', 'en'));
    }

    public function testNonDefaultLocaleWithCompleteTranslations(): void
    {
        $entity = new TestTranslatableEntity();
        $entity->addTranslation(new TestTranslation('fr', 'title', 'Bonjour'));
        $entity->addTranslation(new TestTranslation('fr', 'content', '<p>Monde</p>'));

        $this->assertTrue($this->validator->hasCompleteTranslation($entity, 'fr', 'en'));
    }

    public function testNonDefaultLocaleWithMissingTranslation(): void
    {
        $entity = new TestTranslatableEntity();
        $entity->addTranslation(new TestTranslation('fr', 'title', 'Bonjour'));
        // content translation missing

        $this->assertFalse($this->validator->hasCompleteTranslation($entity, 'fr', 'en'));
    }

    public function testNonDefaultLocaleWithEmptyTranslation(): void
    {
        $entity = new TestTranslatableEntity();
        $entity->addTranslation(new TestTranslation('fr', 'title', 'Bonjour'));
        $entity->addTranslation(new TestTranslation('fr', 'content', ''));

        $this->assertFalse($this->validator->hasCompleteTranslation($entity, 'fr', 'en'));
    }

    public function testDifferentLocalesAreIndependent(): void
    {
        $entity = new TestTranslatableEntity();
        $entity->addTranslation(new TestTranslation('fr', 'title', 'Bonjour'));
        $entity->addTranslation(new TestTranslation('fr', 'content', '<p>Monde</p>'));
        // No 'de' translations

        $this->assertTrue($this->validator->hasCompleteTranslation($entity, 'fr', 'en'));
        $this->assertFalse($this->validator->hasCompleteTranslation($entity, 'de', 'en'));
    }
}

/**
 * Test entity with translatable fields.
 */
class TestTranslatableEntity implements TranslatableInterface
{
    use TranslatableTrait;

    #[TextField(translatable: true)]
    public ?string $title = null;

    #[HtmlField(translatable: true)]
    public ?string $content = null;

    public ?string $slug = null;

    /** @var ArrayCollection<int, TestTranslation> */
    private ArrayCollection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getTranslations(): ArrayCollection
    {
        return $this->translations;
    }

    public function addTranslation(TestTranslation $translation): void
    {
        $this->translations->add($translation);
    }
}

/**
 * Test entity without translatable fields.
 */
class TestNonTranslatableEntity implements TranslatableInterface
{
    use TranslatableTrait;

    public ?string $name = null;
}

/**
 * Minimal translation entity for testing.
 */
class TestTranslation extends AbstractPersonalTranslation
{
    protected $object;

    public function __construct(string $locale, string $field, string $content)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($content);
    }
}
