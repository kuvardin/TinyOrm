<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Values\ColumnValue;
use PDOStatement;
use RuntimeException;
use PDO;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class Parameters
{
    /**
     * @var array<string,mixed>
     */
    protected array $parameters = [];

    /**
     * @var array<string,int>
     */
    protected array $types = [];

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

    public function push(string $name, mixed $value, ?int $type = null): self
    {
        if (array_key_exists($name, $this->parameters)) {
            throw new RuntimeException("Parameter with name $name already set");
        }

        if ($type === null && is_bool($value)) {
            $type = PDO::PARAM_BOOL;
        }

        $this->parameters[$name] = $value;
        if ($type !== null) {
            $this->types[$name] = $type;
        }

        return $this;
    }

    public function pushValue(mixed $value, ?int $type = null): string
    {
        $name = $this->generateName();
        $this->push($name, $value, $type);
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

    public function bind(PDOStatement $statement): void
    {
        foreach ($this->parameters as $name => $value) {
            if ($value instanceof ColumnValue) {
                $value = $value->getValueSql($this);
            }

            if (isset($this->types[$name])) {
                $statement->bindValue($name, $value, $this->types[$name]);
            } else {
                $statement->bindValue($name, $value);
            }
        }
    }

    public function isEmpty(): bool
    {
        return $this->parameters === [];
    }
}