<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use RuntimeException;

class Parameters
{
    protected array $parameters = [];

    public static function fromArray(array $array): self
    {
        $result = new self;
        foreach ($array as $key => $value) {
            if (!is_string($key)) {
                throw new RuntimeException('Array must be associative');
            }

            $result->push($key, $value);
        }

        return $result;
    }

    public function push(string $name, mixed $value): self
    {
        if (array_key_exists($name, $this->parameters)) {
            throw new RuntimeException("Parameter with name $name already set");
        }

        $this->parameters[$name] = $value;
        return $this;
    }

    public function pushValue(mixed $value): string
    {
        $name = $this->generateName();
        $this->push($name, $value);
        return $name;
    }

    public function checkExists(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    public function generateName(): string
    {
        do {
            $result = ':param_' . rand(0, PHP_INT_MAX);
        } while ($this->checkExists($result));

        return $result;
    }

    public function toArray(): array
    {
        return $this->parameters;
    }
}