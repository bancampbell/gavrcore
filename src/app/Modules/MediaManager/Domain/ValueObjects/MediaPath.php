<?php

namespace Modules\MediaManager\Domain\ValueObjects;

class MediaPath
{
    private string $value;

    private const MAX_DEPTH = 10;

    public function __construct(string $path)
    {
        $this->value = $this->normalize($path);
    }

    private function normalize(string $path): string
    {
        $path = trim($path);
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('/\/+/', '/', $path);
        $path = trim($path, '/');

        // Защита от path traversal
        $segments = explode('/', $path);
        foreach ($segments as $segment) {
            if ($segment === '..' || $segment === '.') {
                throw new \InvalidArgumentException('Path segments ".." and "." are not allowed');
            }
        }

        // Проверка глубины
        if (count($segments) > self::MAX_DEPTH) {
            throw new \InvalidArgumentException('Path exceeds maximum depth of ' . self::MAX_DEPTH);
        }

        return $path;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getParent(): self
    {
        $parts = explode('/', $this->value);
        array_pop($parts);

        return new self(implode('/', $parts));
    }

    public function getName(): string
    {
        $parts = explode('/', $this->value);
        return end($parts) ?: '';
    }

    public function getExtension(): string
    {
        $name = $this->getName();

        if (str_starts_with($name, '.')) {
            return '';
        }

        $parts = explode('.', $name);
        return count($parts) > 1 ? strtolower(end($parts)) : '';
    }

    public function isRoot(): bool
    {
        return empty($this->value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
