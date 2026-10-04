<?php

namespace Tests\Unit;

use App\Support\RichText;
use PHPUnit\Framework\TestCase;

/** PRD §32 — CMS rich text is sanitised before storage. */
class RichTextTest extends TestCase
{
    public function test_keeps_trix_formatting(): void
    {
        $html = '<div><strong>Bold</strong> <em>it</em></div><ul><li>One</li></ul><h1>Head</h1><a href="https://moaum.test/x">link</a>';

        $clean = RichText::sanitize($html);

        $this->assertStringContainsString('<strong>Bold</strong>', $clean);
        $this->assertStringContainsString('<li>One</li>', $clean);
        $this->assertStringContainsString('<h1>Head</h1>', $clean);
        $this->assertStringContainsString('href="https://moaum.test/x"', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }

    public function test_strips_scripts_handlers_and_javascript_urls(): void
    {
        $html = '<div onclick="steal()">Hi<script>alert(1)</script></div><a href="javascript:alert(1)">x</a><img src="/storage/a.webp" onerror="x()"><iframe src="https://evil.test"></iframe>';

        $clean = RichText::sanitize($html);

        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('iframe', $clean);
        $this->assertStringContainsString('src="/storage/a.webp"', $clean);
    }

    public function test_blank_input_becomes_null(): void
    {
        $this->assertNull(RichText::sanitize('   '));
        $this->assertNull(RichText::sanitize(null));
    }

    public function test_plain_text_conversion(): void
    {
        $this->assertSame('Hello world & friends', RichText::toPlainText('<div>Hello</div><div>world &amp; <b>friends</b></div>'));
    }
}
