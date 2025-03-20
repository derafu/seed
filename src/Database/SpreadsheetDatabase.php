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
use Derafu\Seed\Schema\Target\SpreadsheetSchemaTarget;
use Derafu\Spreadsheet\Contract\SpreadsheetDumperInterface;
use Derafu\Spreadsheet\Contract\SpreadsheetInterface;
use Derafu\Spreadsheet\Contract\SpreadsheetLoaderInterface;
use Derafu\Spreadsheet\SpreadsheetDumper;
use Derafu\Spreadsheet\SpreadsheetLoader;
use InvalidArgumentException;

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
        private ?SchemaSourceInterface $schemaSource = null,
        private ?SpreadsheetLoaderInterface $loader = null,
        private ?SpreadsheetDumperInterface $dumper = null
    ) {
        foreach ($spreadsheet->getSheets() as $sheet) {
            $spreadsheet->addSheet($sheet->toAssociative());
        }

        parent::__construct($spreadsheet, $options);

        $this->schemaSource = $schemaSource ?? new SpreadsheetSchemaSource();
        $this->loader = $loader ?? new SpreadsheetLoader();
        $this->dumper = $dumper ?? new SpreadsheetDumper();
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
        parent::load($source, $options);

        unset($this->schema);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function createSchema(): SchemaInterface
    {
        return $this->schemaSource->extractSchema($this->connection);
    }

    /**
     * {@inheritDoc}
     */
    protected function loadFromDump(string $source, array $options = []): self
    {
        // Get the file format of the source dump. Required to load the dump.
        $format = $options['format'] ?? throw new InvalidArgumentException(
            'Format is required for loading from dump in a spreadsheet database.'
        );

        // Load the source spreadsheet from the dump.
        $sourceSpreadsheet = $this->loader->loadFromString($source, $format);

        // Load the source spreadsheet into the current connection.
        return $this->load($sourceSpreadsheet, $options);
    }

    /**
     * {@inheritDoc}
     */
    protected function loadFromArray(array $source, array $options = []): self
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function loadFromDatabase(
        DatabaseInterface $source,
        array $options = []
    ): self {
        // Get the source spreadsheet from the source database.
        $sourceSpreadsheet = $source->spreadsheet();

        // Convert the sheets of the source spreadsheet to associative arrays.
        foreach ($sourceSpreadsheet->getSheets() as $sheet) {
            $sourceSpreadsheet->addSheet($sheet->toAssociative());
        }

        // Drop the database if requested. This is achieved by replacing the
        // current connection with the source spreadsheet.
        // This method keeps nothing from the current connection.
        $dropDatabase = $options['dropDatabase'] ?? false;
        $dropTables = $options['dropTables'] ?? false;
        if ($dropDatabase || $dropTables) {
            $this->connection = $sourceSpreadsheet;
            return $this;
        }

        // This will create a new connection with a new spreadsheet that will
        // keep only the schema from the loaded $source.
        $dropData = $options['dropData'] ?? false;
        $schemaSheetName = $options['schemaSheetName'] ?? '__schema';
        if ($dropData) {
            $schemaTarget = new SpreadsheetSchemaTarget($schemaSheetName);
            $this->connection = $schemaTarget->applySchema($source->schema());
            return $this;
        }

        // Nothing to drop. We keep the current connection and load the data.
        // TODO: Implement this. It must load each sheet without dropping the
        // current data. Just add or update the data.
        // WARNING: For NOW, we just load the data from the source spreadsheet.
        // And lost the current data.
        $this->connection = $sourceSpreadsheet;

        return $this;
    }
}
