<?php

declare(strict_types=1);

namespace Kuvardin\TinyOrm;

use Kuvardin\TinyOrm\Enums\RuleForSavingChanges;
use Kuvardin\TinyOrm\Exception\AlreadyExists;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * @package Kuvardin\TinyOrm
 * @author Maxim Kuvardin <maxim@kuvard.in>
 */
class Connection extends PDO
{
    private static ?self $connection_default = null;

    /**
     * @var string[]
     */
    private static array $connection_ids = [];

    protected ?QueryBuilder $query_builder = null;
    protected ?ExpressionBuilder $expression_builder = null;

    private ?string $connection_id = null;

    public RuleForSavingChanges $rule_for_saving_changes = RuleForSavingChanges::ThrowExceptionInDestructor;
    public bool $remove_entity_from_cache_on_destructor = false;

    public ?string $last_query = null;
    public ?array $last_query_parameters = null;

    public static function create(
        string $adapter,
        string $host,
        string $port,
        string $base,
        string $username,
        string $password,
        array $options = null,
    ): self
    {
        $dsn = sprintf(
            "%s:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $adapter,
            $host,
            $port,
            $base,
            $username,
            $password,
        );

        return new self($dsn, options: $options);
    }

    public function getConnectionId(): string
    {
        if ($this->connection_id === null) {
            do {
                $this->connection_id = (string)rand(0, PHP_INT_MAX);
            } while (in_array($this->connection_id, self::$connection_ids));

            self::$connection_ids[] = $this->connection_id;
        }

        return $this->connection_id;
    }

    public static function getConnectionDefault(): ?self
    {
        return self::$connection_default;
    }

    public static function requireConnectionDefault(): self
    {
        if (self::$connection_default === null) {
            throw new RuntimeException('PDO connection default instance was not set');
        }

        return self::$connection_default;
    }

    public static function setConnectionDefault(?self $connection_default): void
    {
        self::$connection_default = $connection_default;
    }

    /**
     * @throws PDOException
     * @throws AlreadyExists
     */
    public function executeFinalQuery(FinalQuery $query): PDOStatement
    {
        $stmt = $this->prepare($query->value);
        if ($stmt === false) {
            throw new RuntimeException("Error preparing query: {$query->value}");
        }

        if ($query->parameters !== null && !$query->parameters->isEmpty()) {
            $query->parameters->bind($stmt);
        }

        $this->last_query = $stmt->queryString;
        $this->last_query_parameters = $query->parameters?->toArray() ?? [];

        try {
            $stmt->execute();
        } catch (PDOException $pdo_exception) {
            if ($pdo_exception->getCode() == '23505') {
                $already_exists = new AlreadyExists(
                    $pdo_exception->getMessage(),
                    (int)$pdo_exception->getCode(),
                    $pdo_exception->getPrevious()
                );

                $already_exists->errorInfo = $pdo_exception->errorInfo;
                throw $already_exists;
            }

            throw $pdo_exception;
        }

        return $stmt;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->query_builder ??= new QueryBuilder($this);
    }

    /**
     * Alias for getQueryBuilder()
     */
    public function qb(): QueryBuilder
    {
        return $this->getQueryBuilder();
    }

    public function getExpressionBuilder(): ExpressionBuilder
    {
        return $this->expression_builder ??= new ExpressionBuilder;
    }

    /**
     * Alias for getExpressionBuilder()
     */
    public function expr(): ExpressionBuilder
    {
        return $this->getExpressionBuilder();
    }
}