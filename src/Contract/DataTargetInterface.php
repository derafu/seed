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

use RuntimeException;

/**
 * DataTarget is responsible for applying data to a specific target.
 *
 * This interface defines the minimum contract for any data target adapter that
 * can insert or update data in a target system.
 */
interface DataTargetInterface
{
    /**
     * Apply the given data rows to the specified table.
     *
     * @param mixed $target The target to apply data to.
     * @param string $table The name of the table to insert/update data in.
     * @param array $rows An array representing rows of data.
     * @return int Number of affected rows.
     * @throws RuntimeException When the data cannot be applied.
     */
    public function applyTableData(mixed $target, string $table, array $rows): int;
}
