<?php

declare(strict_types=1);

/**
 * Derafu: ETL - From spreadsheets to databases seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\ETL\Contract;

use Derafu\Spreadsheet\Contract\SpreadsheetInterface;

/**
 * Database interface.
 *
 * This interface defines the methods that a database must implement.
 */
interface DatabaseInterface
{
    /**
     * Get the connection.
     *
     * Example: PDO instance, SpreadsheetInterface instance, etc.
     *
     * @return mixed The connection.
     */
    public function connection(): mixed;

    /**
     * Get the options.
     *
     * This are the options of the database, not only for the connection.
     *
     * @return array The options.
     */
    public function options(): array;

    /**
     * Get the database schema.
     *
     * If the schema is not explicitly set, it will be inferred from the
     * connection.
     *
     * @return SchemaInterface The schema of the database.
     */
    public function schema(): SchemaInterface;

    /**
     * Dump the database.
     *
     * @param array $options The options for the dump.
     * @return string The dump.
     */
    public function dump(array $options = []): string;

    /**
     * Save the database.
     *
     * @param string $file The file.
     * @param array $options The options for the save.
     * @return self The database.
     */
    public function save(string $file, array $options = []): self;

    /**
     * Get the data of the database.
     *
     * @param array $options The options for the data.
     * @return array<string, array<string, mixed>> The data.
     */
    public function data(array $options = []): array;

    /**
     * Convert the database to a spreadsheet.
     *
     * @param array $options The options for the conversion.
     * @return SpreadsheetInterface The spreadsheet.
     */
    public function spreadsheet(array $options = []): SpreadsheetInterface;

    /**
     * Load the database from a source.
     *
     * $source can be:
     *
     *   - string: dump of a database of the same type of the current database.
     *   - array: a list of tables with their data.
     *   - SpreadsheetInterface: a spreadsheet.
     *   - DatabaseInterface: another database.
     *
     * @param string|array<string, array<string, mixed>>|SpreadsheetInterface|DatabaseInterface $source The source.
     * @param array $options The options for the load.
     * @return self The database.
     */
    public function load(
        string|array|SpreadsheetInterface|DatabaseInterface $source,
        array $options = []
    ): self;
}
