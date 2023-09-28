<?php

declare(strict_types=1);

namespace Kuvardin\test;

use Kuvardin\TinyOrm\Conditions\ConditionExpression;
use Kuvardin\TinyOrm\Connection;
use Kuvardin\TinyOrm\EntityAbstract;
use Kuvardin\TinyOrm\Table;

class ExampleEntity extends EntityAbstract
{
    public const COL_CODE = 'code';
    public const COL_TITLE = 'title';
    public const COL_DESCRIPTION = 'description';
    public const COL_COUNTER = 'counter';

    protected string $code;
    protected string $title;
    protected string $description;
    protected int $counter;

    public function __construct(Connection $connection, Table $table, array $data)
    {
        parent::__construct($connection, $table, $data);
        $this->code = $data[self::COL_CODE];
        $this->title = $data[self::COL_CODE];
        $this->description = $data[self::COL_DESCRIPTION];
        $this->counter = $data[self::COL_COUNTER];
    }

    public static function getEntityTableDefault(): Table
    {
        return new Table('example_table');
    }

    public static function create(
        string $code,
        string $title,
        string $description,
        int $counter,
        Connection $connection = null,
        Table $table = null,
    ): self
    {
        return self::createByValuesArray(
            values_array: [
                self::COL_CODE => $code,
                self::COL_TITLE => $title,
                self::COL_DESCRIPTION => $description,
                self::COL_COUNTER => $counter,
            ],
            connection: $connection,
            table: $table,
        );
    }

    public static function findOneByCode(string $code, Connection $connection, Table $table = null): ?self
    {
        return self::findOneByConditions(
            conditions: new ConditionExpression($connection->expr()->equal(self::COL_CODE, $code)),
            connection: $connection,
            table: $table,
        );
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->setFieldValue(self::COL_CODE, $this->code, $code);
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->setFieldValue(self::COL_TITLE, $this->title, $title);
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->setFieldValue(self::COL_DESCRIPTION, $this->description, $description);
        return $this;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function setCounter(int $counter): self
    {
        $this->setFieldValue(self::COL_COUNTER, $this->counter, $counter);
        return $this;
    }

    public function incrementCounter(int $increment = null): self
    {
        $this->setFieldValue(
            column: self::COL_COUNTER,
            current_value: $this->counter,
            new_value: $this->connection->expr()->sum(
                operand_first: $this->entity_table->getColumn(self::COL_COUNTER),
                operand_second: $increment ?? 1,
            ),
        );

        return $this;
    }
}