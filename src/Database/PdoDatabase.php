<?php

declare(strict_types=1);

/**
 * Derafu: ETL - From spreadsheets to databases seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\ETL\Database;

use Derafu\ETL\Abstract\AbstractDatabase;
use Derafu\ETL\Contract\DatabaseInterface;
use Derafu\ETL\Contract\SchemaInterface;
use Derafu\Spreadsheet\Contract\SpreadsheetInterface;
use InvalidArgumentException;
use PDO;
use RuntimeException;

/**
 * Database implementation for PDO connections.
 *
 * This class is just a wrapper around the DoctrineDatabase class. This is a
 * convenient way to connect to a database using a PDO connection or PDO
 * connection parameters.
 */
final class PdoDatabase extends AbstractDatabase implements DatabaseInterface
{
    /**
     * Constructor.
     *
     * The $pdo can be a string, an array or a PDO instance.
     *
     *   - If it is a string, it is used as the DSN for the PDO connection.
     *   - If it is an array, it is used as the parameters for the PDO connection.
     *   - If it is a PDO instance, it is used as the PDO connection.
     *
     * @param string|array|PDO $pdo The PDO connection.
     * @param array $options The options for the database (not only for PDO).
     */
    public function __construct(
        string|array|PDO $pdo,
        array $options = []
    ) {
        if (is_string($pdo)) {
            $pdo = [
                'dsn' => $pdo,
            ];
        }

        if (is_array($pdo)) {
            $pdo = new PDO(
                dsn: $pdo['dsn']
                    ?? throw new InvalidArgumentException(
                        'DSN is required in PDO connection.'
                    ),
                username: $pdo['username'] ?? null,
                password: $pdo['password'] ?? null,
                options: $pdo['options'] ?? [],
            );
        }

        parent::__construct($pdo, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function dump(array $options = []): string
    {
        throw new RuntimeException('PdoDatabase::dump() not implemented.');
    }

    /**
     * {@inheritDoc}
     */
    public function data(array $options = []): array
    {
        throw new RuntimeException('PdoDatabase::data() not implemented.');
    }

    /**
     * {@inheritDoc}
     */
    public function spreadsheet(array $options = []): SpreadsheetInterface
    {
        throw new RuntimeException('PdoDatabase::spreadsheet() not implemented.');
    }

    /**
     * {@inheritDoc}
     */
    protected function createSchema(): SchemaInterface
    {
        throw new RuntimeException('PdoDatabase::createSchema() not implemented.');
    }

    /**
     * {@inheritDoc}
     */
    protected function loadFromDump(string $source, array $options = []): self
    {
        throw new RuntimeException('PdoDatabase::loadFromDump() not implemented.');
    }

    /**
     * {@inheritDoc}
     */
    protected function loadFromArray(array $source, array $options = []): self
    {
        throw new RuntimeException('PdoDatabase::loadFromArray() not implemented.');
    }

    /**
     * {@inheritDoc}
     */
    protected function loadFromDatabase(
        DatabaseInterface $source,
        array $options = []
    ): self {
        throw new RuntimeException('PdoDatabase::loadFromDatabase() not implemented.');
    }
}
