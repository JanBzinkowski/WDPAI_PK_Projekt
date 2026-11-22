<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Core\Session;
use PHPUnit\Framework\TestCase;

final class SessionTest extends TestCase
{
    private Session $session;

    protected function setUp(): void
    {
        $_SESSION = [];
        $config = new Config('dev', true, 'http://localhost', 'UTC', 8080);
        $this->session = new Session($config, 1800);
    }

    public function testSetGetRemove(): void
    {
        $this->session->set('key', 'value');
        $this->assertTrue($this->session->has('key'));
        $this->assertSame('value', $this->session->get('key'));

        $this->session->remove('key');
        $this->assertFalse($this->session->has('key'));
        $this->assertSame('default', $this->session->get('key', 'default'));
    }

    public function testFlashMessages(): void
    {
        $this->session->flash('notice', 'Operacja udana');

        $firstRead = $this->session->getFlash('notice');
        $this->assertSame('Operacja udana', $firstRead);

        $secondRead = $this->session->getFlash('notice');
        $this->assertNull($secondRead);
    }

    public function testIdleTimeoutExpiresSession(): void
    {
        $shortSession = new Session(new Config('dev', true, 'http://localhost', 'UTC', 8080), 10);
        $_SESSION['user_id'] = 123;
        $_SESSION['_last_activity'] = time() - 20; // 20 seconds ago, timeout is 10s

        // When starting or checking timeout, session is cleared
        $reflection = new \ReflectionMethod($shortSession, 'checkIdleTimeout');
        $reflection->invoke($shortSession);

        $this->assertEmpty($_SESSION);
    }
}
