<?php

declare(strict_types=1);

/**
 * Derafu: Seed - From spreadsheets to databases seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Seed;

use Derafu\Seed\Contract\ColumnInterface;
use Derafu\Seed\Contract\ForeignKeyInterface;
use Derafu\Seed\Contract\IndexInterface;
use Derafu\Seed\Contract\TableInterface;

/**
 * Implementation of a database table.
 */
final class Table implements TableInterface
{
    /**
     * The name of the table.
     *
     * @var string
     */
    private string $name;

    /**
     * The columns of the table.
     *
     * @var ColumnInterface[]
     */
    private array $columns = [];

    /**
     * The primary key of the table.
     *
     * @var string[]
     */
    private array $primaryKey = [];

    /**
     * The foreign keys of the table.
     *
     * @var ForeignKeyInterface[]
     */
    private array $foreignKeys = [];

    /**
     * The indexes of the table.
     *
     * @var IndexInterface[]
     */
    private array $indexes = [];

    /**
     * Constructor.
     *
     * @param string $name The table name.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * {@inheritDoc}
     */
    public function getColumn(string $name): ?ColumnInterface
    {
        return $this->columns[$name] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function addColumn(ColumnInterface $column): self
    {
        $this->columns[$column->getName()] = $column;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasColumn(string $name): bool
    {
        return isset($this->columns[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getPrimaryKey(): array
    {
        return $this->primaryKey;
    }

    /**
     * {@inheritDoc}
     */
    public function setPrimaryKey(array $columnNames): self
    {
        $this->primaryKey = $columnNames;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getForeignKeys(): array
    {
        return $this->foreignKeys;
    }

    /**
     * {@inheritDoc}
     */
    public function addForeignKey(ForeignKeyInterface $foreignKey): self
    {
        $this->foreignKeys[] = $foreignKey;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex(string $name): ?IndexInterface
    {
        return $this->indexes[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function addIndex(IndexInterface $index): self
    {
        $this->indexes[$index->getName()] = $index;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasIndex(string $name): bool
    {
        return isset($this->indexes[$name]);
    }
}
