<?php

declare(strict_types=1);

/**
 * Derafu: Seed - From spreadsheets to databases seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Seed\Database;

use Derafu\Seed\Abstract\AbstractDatabase;
use Derafu\Seed\Contract\DatabaseInterface;
use Derafu\Spreadsheet\Contract\SpreadsheetInterface;
use Derafu\Spreadsheet\Spreadsheet;
use InvalidArgumentException;
use PDO;

/**
 * Database implementation for PDO connections.
 *
 * This class is used to connect to a database using a PDO connection.
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
        // TODO: Implement dump() method.

        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function save(string $file, array $options = []): self
    {
        file_put_contents($file, $this->dump($options));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function data(array $options = []): array
    {
        // TODO: Implement data() method.

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function spreadsheet(array $options = []): SpreadsheetInterface
    {
        // TODO: Implement spreadsheet() method.

        return new Spreadsheet();
    }

    /**
     * {@inheritDoc}
     */
    public function load(
        string|array|SpreadsheetInterface|DatabaseInterface $source,
        array $options = []
    ): self {
        // If the source is a spreadsheet, load it.
        if ($source instanceof DatabaseInterface) {
            return $this->loadFromDatabase($source, $options);
        }

        // TODO: Implement load() method.

        return $this;
    }

    /**
     * Load the database from a spreadsheet.
     *
     * @param DatabaseInterface $sourceDatabase The source database.
     * @param array $options The options for the load.
     * @return self The database.
     */
    private function loadFromDatabase(
        DatabaseInterface $sourceDatabase,
        array $options = []
    ): self {
        $sourceSchema = $sourceDatabase->schema();
        $sourceConnection = $sourceDatabase->connection();

        // TODO: Implement this method.

        return $this;
    }
}
