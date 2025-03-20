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

/**
 * Database manager interface.
 */
interface DatabaseManagerInterface
{
    /**
     * Connect to a database.
     *
     * @param array $options The options for the database connection.
     * @return DatabaseInterface The database representation.
     */
    public function connect(array $options = []): DatabaseInterface;
}
