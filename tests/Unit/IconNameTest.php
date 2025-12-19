<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tunr\FluxFontAwesome\Support\IconName;

class IconNameTest extends TestCase
{
    #[Test]
    public function it_validates_simple_icon_names(): void
    {
        $this->assertEquals('user', IconName::validate('user'));
        $this->assertEquals('home', IconName::validate('home'));
        $this->assertEquals('github', IconName::validate('github'));
    }

    #[Test]
    public function it_validates_hyphenated_icon_names(): void
    {
        $this->assertEquals('user-circle', IconName::validate('user-circle'));
        $this->assertEquals('arrow-up-right', IconName::validate('arrow-up-right'));
        $this->assertEquals('chevron-double-down', IconName::validate('chevron-double-down'));
    }

    #[Test]
    public function it_validates_icon_names_with_numbers(): void
    {
        $this->assertEquals('1', IconName::validate('1'));
        $this->assertEquals('360-degrees', IconName::validate('360-degrees'));
        $this->assertEquals('text-width', IconName::validate('text-width'));
    }

    #[Test]
    public function it_normalizes_to_lowercase(): void
    {
        $this->assertEquals('user', IconName::validate('User'));
        $this->assertEquals('user', IconName::validate('USER'));
        $this->assertEquals('github', IconName::validate('GitHub'));
    }

    #[Test]
    public function it_trims_whitespace(): void
    {
        $this->assertEquals('user', IconName::validate(' user '));
        $this->assertEquals('user', IconName::validate("\tuser\n"));
    }

    #[Test]
    #[DataProvider('pathTraversalProvider')]
    public function it_rejects_path_traversal_attempts(string $maliciousName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('path traversal');

        IconName::validate($maliciousName);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function pathTraversalProvider(): array
    {
        return [
            'double dot' => ['../etc/passwd'],
            'double dot in middle' => ['user/../admin'],
            'forward slash' => ['user/admin'],
            'backslash' => ['user\\admin'],
            'encoded traversal' => ['..%2F..%2Fetc'],
        ];
    }

    #[Test]
    #[DataProvider('hiddenFileProvider')]
    public function it_rejects_hidden_files(string $hiddenName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot start with a dot');

        IconName::validate($hiddenName);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function hiddenFileProvider(): array
    {
        return [
            'dot env' => ['.env'],
            'dot git' => ['.git'],
            'dot hidden' => ['.hidden'],
        ];
    }

    #[Test]
    #[DataProvider('invalidFormatProvider')]
    public function it_rejects_invalid_formats(string $invalidName): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('only lowercase letters');

        IconName::validate($invalidName);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidFormatProvider(): array
    {
        return [
            'underscore' => ['user_icon'],
            'special chars' => ['user@icon'],
            'spaces' => ['user icon'],
            'leading hyphen' => ['-user'],
            'trailing hyphen' => ['user-'],
            'double hyphen' => ['user--icon'],
        ];
    }

    #[Test]
    public function it_rejects_empty_names(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be empty');

        IconName::validate('');
    }

    #[Test]
    public function it_rejects_whitespace_only_names(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be empty');

        IconName::validate('   ');
    }

    #[Test]
    public function is_valid_returns_true_for_valid_names(): void
    {
        $this->assertTrue(IconName::isValid('user'));
        $this->assertTrue(IconName::isValid('user-circle'));
        $this->assertTrue(IconName::isValid('arrow-up-right-from-square'));
    }

    #[Test]
    public function is_valid_returns_false_for_invalid_names(): void
    {
        $this->assertFalse(IconName::isValid('../etc/passwd'));
        $this->assertFalse(IconName::isValid('.env'));
        $this->assertFalse(IconName::isValid('user/admin'));
        $this->assertFalse(IconName::isValid(''));
    }
}
