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
use Derafu\Seed\Contract\SchemaInterface;
use Derafu\Seed\Contract\SchemaSourceInterface;
use Derafu\Seed\Schema\Source\SpreadsheetSchemaSource;
use Derafu\Spreadsheet\Contract\SpreadsheetDumperInterface;
use Derafu\Spreadsheet\Contract\SpreadsheetInterface;
use Derafu\Spreadsheet\SpreadsheetDumper;

/**
 * Database implementation for spreadsheets.
 */
final class SpreadsheetDatabase extends AbstractDatabase implements DatabaseInterface
{
    /**
     * Constructor.
     *
     * @param SpreadsheetInterface $spreadsheet The spreadsheet.
     * @param array $options The options for the database (not only for spreadsheet).
     */
    public function __construct(
        SpreadsheetInterface $spreadsheet,
        array $options = [],
        private ?SpreadsheetDumperInterface $dumper = null,
        private ?SchemaSourceInterface $schemaSource = null
    ) {
        foreach ($spreadsheet->getSheets() as $sheet) {
            $spreadsheet->addSheet($sheet->toAssociative());
        }

        parent::__construct($spreadsheet, $options);

        $this->dumper = $dumper ?? new SpreadsheetDumper();
        $this->schemaSource = $schemaSource ?? new SpreadsheetSchemaSource();
    }

    /**
     * {@inheritDoc}
     */
    public function schema(): SchemaInterface
    {
        if (!isset($this->schema)) {
            $this->schema = $this->schemaSource->extractSchema($this->connection);
        }

        return $this->schema;
    }

    /**
     * {@inheritDoc}
     */
    public function dump(array $options = []): string
    {
        $format = $options['format'] ?? $this->options['format'] ?? 'xlsx';

        return $this->dumper->dumpToString($this->connection, $format);
    }

    /**
     * {@inheritDoc}
     */
    public function save(string $file, array $options = []): self
    {
        $format = $options['format'] ?? $this->options['format'] ?? 'xlsx';

        $this->dumper->dumpToFile($this->connection, $file, $format);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function data(array $options = []): array
    {
        $table = $options['table'] ?? null;

        if ($table === null) {
            $tables = $this->connection->toArray();
        } else {
            $tables = [$table => $this->connection->getSheet($table)->toArray()];
        }

        $data = [];
        foreach ($tables as $name => $info) {
            $data[$name] = $info['rows'];
        }

        return $table === null ? $data : $data[$table];
    }

    /**
     * {@inheritDoc}
     */
    public function spreadsheet(array $options = []): SpreadsheetInterface
    {
        return $this->connection;
    }

    /**
     * {@inheritDoc}
     */
    public function load(
        string|array|SpreadsheetInterface|DatabaseInterface $source,
        array $options = []
    ): self {
        // TODO: Implement load() method.

        // Invalidate the schema cache, just in case.
        unset($this->schema);

        return $this;
    }
}
