<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Scaleum\Http\InboundRequest;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

final class InboundRequestCleanKeyTest extends TestCase
{
    public function testAcceptsDottedAndNormalizedNames(): void
    {
        foreach (['ui.theme.user', 'ui_theme_user', 'key:part/value-name'] as $key) {
            self::assertSame($key, InboundRequestCleanKeyProbe::key($key));
        }
    }

    public function testStillRejectsInvalidCharacters(): void
    {
        foreach (['', 'invalid key', 'key<script>', 'key=value', 'key;value', "key\r\nvalue"] as $key) {
            try {
                InboundRequestCleanKeyProbe::key($key);
                self::fail('Invalid key was accepted');
            } catch (ERuntimeError $exception) {
                self::assertSame('Disallowed Key Characters', $exception->getMessage());
            }
        }
    }

    public function testSanitizesBothRuntimeCookieNames(): void
    {
        $cookies = $_COOKIE;
        $get = $_GET;
        $post = $_POST;
        $server = $_SERVER;
        try {
            $_COOKIE = ['ui.theme.user' => 'dark', 'ui_theme_user' => 'dark'];
            $_GET = [];
            $_POST = [];
            $_SERVER['PHP_SELF'] = '/index.php';
            InboundRequestCleanKeyProbe::sanitizeGlobals();
            self::assertSame(['ui.theme.user' => 'dark', 'ui_theme_user' => 'dark'], $_COOKIE);
        } finally {
            $_COOKIE = $cookies;
            $_GET = $get;
            $_POST = $post;
            $_SERVER = $server;
        }
    }
}

final class InboundRequestCleanKeyProbe extends InboundRequest
{
    public static function key(string $key): string
    {
        return parent::cleanKey($key);
    }

    public static function sanitizeGlobals(): void
    {
        parent::sanitize();
    }
}
