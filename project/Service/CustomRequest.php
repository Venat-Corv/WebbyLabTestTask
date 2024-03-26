<?php

namespace App\Service;

class CustomRequest
{
    public function __construct(
        private ?string $method = null,
        private ?array $data = null
    )
    {
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): void
    {
        $this->method = $method;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    public function getValue(?string $key): string|array|null
    {
        return $_REQUEST[$key] ?? null;
    }

    public function getFile(?string $key): mixed
    {
        return $_FILES[$key] ?? null;
    }

    public function isMethod(string $method): bool
    {
        return strtolower($method) === strtolower($this->method);
    }
}