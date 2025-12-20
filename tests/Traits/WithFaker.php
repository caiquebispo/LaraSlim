<?php

namespace Tests\Traits;

/**
 * WithFaker Trait
 * 
 * Provides fake data generation for tests.
 */
trait WithFaker
{
    /**
     * Generate a random string
     */
    protected function fakeString(int $length = 10): string
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    /**
     * Generate a random email
     */
    protected function fakeEmail(): string
    {
        return $this->fakeString(8) . '@example.com';
    }

    /**
     * Generate a random name
     */
    protected function fakeName(): string
    {
        $firstNames = ['John', 'Jane', 'Bob', 'Alice', 'Charlie', 'Diana', 'Eve', 'Frank'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis'];
        
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    /**
     * Generate a random integer
     */
    protected function fakeInt(int $min = 1, int $max = 1000): int
    {
        return random_int($min, $max);
    }

    /**
     * Generate a random boolean
     */
    protected function fakeBool(): bool
    {
        return (bool) random_int(0, 1);
    }

    /**
     * Generate a random date
     */
    protected function fakeDate(string $format = 'Y-m-d'): string
    {
        $timestamp = random_int(strtotime('-1 year'), strtotime('+1 year'));
        return date($format, $timestamp);
    }

    /**
     * Generate a random datetime
     */
    protected function fakeDateTime(string $format = 'Y-m-d H:i:s'): string
    {
        $timestamp = random_int(strtotime('-1 year'), strtotime('+1 year'));
        return date($format, $timestamp);
    }

    /**
     * Generate a random URL
     */
    protected function fakeUrl(): string
    {
        return 'https://example.com/' . $this->fakeString(8);
    }

    /**
     * Generate a random UUID
     */
    protected function fakeUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }

    /**
     * Generate a random password (hashed)
     */
    protected function fakePassword(string $plain = 'password'): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }

    /**
     * Generate a random sentence
     */
    protected function fakeSentence(int $words = 5): string
    {
        $wordList = ['the', 'quick', 'brown', 'fox', 'jumps', 'over', 'lazy', 'dog', 'lorem', 'ipsum'];
        $sentence = [];
        
        for ($i = 0; $i < $words; $i++) {
            $sentence[] = $wordList[array_rand($wordList)];
        }
        
        return ucfirst(implode(' ', $sentence)) . '.';
    }

    /**
     * Generate random paragraph
     */
    protected function fakeParagraph(int $sentences = 3): string
    {
        $paragraph = [];
        
        for ($i = 0; $i < $sentences; $i++) {
            $paragraph[] = $this->fakeSentence(random_int(5, 10));
        }
        
        return implode(' ', $paragraph);
    }

    /**
     * Pick a random element from array
     */
    protected function fakeFromArray(array $items): mixed
    {
        return $items[array_rand($items)];
    }
}
