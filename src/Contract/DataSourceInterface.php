<?php

declare(strict_types=1);

/**
 * Derafu: Seed - From spreadsheets to databases seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Seed\Contract;

use RuntimeException;

/**
 * DataSource is responsible for extracting data rows from a specific source.
 *
 * This interface defines the minimum contract for any data source adapter that
 * can extract data for seeding a database.
 */
interface DataSourceInterface
{
    /**
     * Extract data from the source for a specific table.
     *
     * @param mixed $source The source to extract data from.
     * @param string $table The name of the table to extract data for.
     * @return array An array representing rows of data.
     * @throws RuntimeException When the data cannot be extracted.
     */
    public function extractTableData(mixed $source, string $table): array;

    /**
     * Get a list of available table names in this data source.
     *
     * @param mixed $source The source to extract data from.
     * @return string[] Array of table names.
     */
    public function getTableNames(mixed $source): array;

    /**
     * Check if this data source contains data for the specified table.
     *
     * @param mixed $source The source to extract data from.
     * @param string $table The table name to check.
     * @return bool True if data exists for this table, false otherwise.
     */
    public function hasTableData(mixed $source, string $table): bool;
}
