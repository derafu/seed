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

use Derafu\Seed\Contract\IndexInterface;

/**
 * Implementation of a database index.
 */
final class Index implements IndexInterface
{
    /**
     * The name of the index.
     *
     * @var string
     */
    private string $name;

    /**
     * The columns that make up the index.
     *
     * @var string[]
     */
    private array $columns = [];

    /**
     * Whether the index is unique.
     *
     * @var bool
     */
    private bool $unique = false;

    /**
     * Additional flags for the index.
     *
     * @var string[]
     */
    private array $flags = [];

    /**
     * Constructor.
     *
     * @param string $name The index name.
     * @param string[] $columns The column names.
     * @param bool $unique Whether the index is unique.
     * @param string[] $flags Additional flags for the index.
     */
    public function __construct(
        string $name,
        array $columns = [],
        bool $unique = false,
        array $flags = []
    ) {
        $this->name = $name;
        $this->columns = $columns;
        $this->unique = $unique;
        $this->flags = $flags;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * {@inheritdoc}
     */
    public function setColumns(array $columnNames): self
    {
        $this->columns = $columnNames;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    /**
     * {@inheritdoc}
     */
    public function setUnique(bool $unique): self
    {
        $this->unique = $unique;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    /**
     * {@inheritdoc}
     */
    public function setFlags(array $flags): self
    {
        $this->flags = $flags;

        return $this;
    }
}
