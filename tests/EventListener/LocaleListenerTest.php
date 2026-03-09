<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\EventListener;

use Gedmo\Translatable\TranslatableListener;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\EventListener\LocaleListener;
use PsychedCms\Core\Settings\LocaleSettingsProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class LocaleListenerTest extends TestCase
{
    private TranslatableListener $translatableListener;
    private LocaleListener $listener;
    private HttpKernelInterface $kernel;

    protected function setUp(): void
    {
        $this->translatableListener = new TranslatableListener();
        $localeSettings = $this->createLocaleSettingsProvider('en', ['en', 'fr']);
        $this->listener = new LocaleListener($this->translatableListener, $localeSettings);
        $this->kernel = $this->createStub(HttpKernelInterface::class);
    }

    public function testDefaultLocaleWhenNoAcceptLanguage(): void
    {
        $request = Request::create('/api/pages');
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        $this->assertSame('en', $request->getLocale());
    }

    public function testAcceptLanguageFrReturnsFrenchLocale(): void
    {
        $request = Request::create('/api/pages');
        $request->headers->set('Accept-Language', 'fr');
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        $this->assertSame('fr', $request->getLocale());
    }

    public function testQueryParamOverridesAcceptLanguage(): void
    {
        $request = Request::create('/api/pages', 'GET', ['locale' => 'fr']);
        $request->headers->set('Accept-Language', 'en');
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        $this->assertSame('fr', $request->getLocale());
    }

    public function testUnsupportedLocaleFallsBackToDefault(): void
    {
        $request = Request::create('/api/pages');
        $request->headers->set('Accept-Language', 'de');
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        $this->assertSame('en', $request->getLocale());
    }

    public function testUnsupportedQueryParamFallsBackToAcceptLanguage(): void
    {
        $request = Request::create('/api/pages', 'GET', ['locale' => 'de']);
        $request->headers->set('Accept-Language', 'fr');
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        $this->assertSame('fr', $request->getLocale());
    }

    public function testTranslatableListenerLocaleIsSet(): void
    {
        $request = Request::create('/api/pages');
        $request->headers->set('Accept-Language', 'fr');
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        $reflection = new \ReflectionProperty($this->translatableListener, 'locale');
        $this->assertSame('fr', $reflection->getValue($this->translatableListener));
    }

    public function testTranslationFallbackEnabled(): void
    {
        $request = Request::create('/api/pages');
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        $reflection = new \ReflectionProperty($this->translatableListener, 'translationFallback');
        $this->assertTrue($reflection->getValue($this->translatableListener));
    }

    public function testContentLanguageHeaderInResponse(): void
    {
        $request = Request::create('/api/pages');
        $request->setLocale('fr');
        $response = new Response();
        $event = new ResponseEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        $this->listener->onKernelResponse($event);

        $this->assertSame('fr', $response->headers->get('Content-Language'));
    }

    public function testSubRequestIsIgnored(): void
    {
        $request = Request::create('/api/pages');
        $request->headers->set('Accept-Language', 'fr');
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $this->listener->onKernelRequest($event);

        // Locale should not have changed (default is 'en')
        $this->assertSame('en', $request->getLocale());
    }

    public function testSubRequestResponseIsIgnored(): void
    {
        $request = Request::create('/api/pages');
        $request->setLocale('fr');
        $response = new Response();
        $event = new ResponseEvent($this->kernel, $request, HttpKernelInterface::SUB_REQUEST, $response);

        $this->listener->onKernelResponse($event);

        $this->assertNull($response->headers->get('Content-Language'));
    }

    /**
     * @param list<string> $supportedLocales
     */
    private function createLocaleSettingsProvider(string $defaultLocale, array $supportedLocales): LocaleSettingsProvider
    {
        $provider = $this->createStub(LocaleSettingsProvider::class);
        $provider->method('getDefaultLocale')->willReturn($defaultLocale);
        $provider->method('getSupportedLocales')->willReturn($supportedLocales);

        return $provider;
    }
}
