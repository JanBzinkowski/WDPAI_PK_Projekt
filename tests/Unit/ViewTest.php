<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\View;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/src/core/helpers.php';

final class ViewTest extends TestCase
{
    public function testEscapeHelperEscapesXss(): void
    {
        $malicious = '<script>alert("xss")</script>&"\'';
        $escaped = e($malicious);

        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertSame('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;&amp;&quot;&#039;', $escaped);
    }

    public function testViewRendersWithLayoutAndPartials(): void
    {
        $view = new View();
        $output = $view->render('home', ['pageTitle' => 'Test Home Page']);

        $this->assertStringContainsString('<!DOCTYPE html>', $output);
        $this->assertStringContainsString('Test Home Page', $output);
        $this->assertStringContainsString('Flashcards Leitner', $output);
        $this->assertStringContainsString('Witaj w aplikacji do powtórek fiszek', $output);
    }

    public function testViewRendersWithoutLayout(): void
    {
        $view = new View();
        $output = $view->render('home', ['pageTitle' => 'No Layout'], null);

        $this->assertStringNotContainsString('<!DOCTYPE html>', $output);
        $this->assertStringContainsString('No Layout', $output);
    }
}
