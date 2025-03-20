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
 * SchemaSource is responsible for extracting a schema from a specific source.
 *
 * This interface defines the minimum contract for any schema source adapter
 * that can convert an external schema representation into a SchemaInterface.
 */
interface SchemaSourceInterface
{
    /**
     * Extract a schema from the source.
     *
     * @param mixed $source The source to extract the schema from.
     * @return SchemaInterface The extracted schema.
     * @throws RuntimeException When the schema cannot be extracted.
     */
    public function extractSchema(mixed $source): SchemaInterface;
}
