<?php

declare(strict_types=1);

/**
 * Derafu: Seed - From spreadsheets to databases seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Seed\Abstract;

use Derafu\Seed\Contract\DatabaseInterface;
use Derafu\Seed\Contract\SchemaInterface;

/**
 * Abstract database class.
 */
abstract class AbstractDatabase implements DatabaseInterface
{
    /**
     * The database connection.
     *
     * Example: PDO instance, SpreadsheetInterface instance, etc.
     *
     * @var mixed
     */
    protected mixed $connection;

    /**
     * The database options.
     *
     * @var array
     */
    protected array $options;

    /**
     * The database schema.
     *
     * @var SchemaInterface
     */
    protected SchemaInterface $schema;

    /**
     * Constructor.
     *
     * @param mixed $connection The database connection.
     * @param array $options The database options.
     */
    public function __construct(mixed $connection, array $options)
    {
        $this->connection = $connection;
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function connection(): mixed
    {
        return $this->connection;
    }

    /**
     * {@inheritDoc}
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function schema(): SchemaInterface
    {
        return $this->schema;
    }
}
