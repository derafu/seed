<?php

declare(strict_types=1);

/**
 * Derafu: Seed - From spreadsheets to databases seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Seed\Data\Source;

use Derafu\Seed\Contract\DataSourceInterface;
use Derafu\Spreadsheet\Contract\SpreadsheetInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Extracts data from a Derafu Spreadsheet.
 */
class SpreadsheetDataSource implements DataSourceInterface
{
    /**
     * Constructor.
     *
     * @param string $schemaSheetName The name of the schema sheet.
     */
    public function __construct(
        private readonly string $schemaSheetName = '__schema'
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function extractTableData(mixed $spreadsheet, string $table): array
    {
        // Check if spreadsheet is a valid type.
        if (!$spreadsheet instanceof SpreadsheetInterface) {
            throw new InvalidArgumentException(
                '$spreadsheet must be an instance of SpreadsheetInterface.'
            );
        }

        // Check we're not trying to extract data from the schema sheet.
        if ($table === $this->schemaSheetName) {
            throw new InvalidArgumentException(sprintf(
                'Cannot extract data from the schema sheet "%s".',
                $this->schemaSheetName
            ));
        }

        // Check if the requested table exists as a sheet.
        if (!$spreadsheet->hasSheet($table)) {
            throw new RuntimeException(sprintf(
                'Table "%s" does not exist in the spreadsheet.',
                $table
            ));
        }

        // Get the sheet for this table.
        $sheet = $spreadsheet->getSheet($table)->toAssociative();

        // Return all data rows.
        return $sheet->getDataRows();
    }

    /**
     * {@inheritDoc}
     */
    public function getTableNames(mixed $spreadsheet): array
    {
        // Check if spreadsheet is a valid type.
        if (!$spreadsheet instanceof SpreadsheetInterface) {
            throw new InvalidArgumentException(
                '$spreadsheet must be an instance of SpreadsheetInterface.'
            );
        }

        // Get all sheet names.
        $sheetNames = $spreadsheet->getSheetNames();

        // Filter out the schema sheet.
        return array_values(array_filter(
            $sheetNames,
            fn ($sheetName) => $sheetName !== $this->schemaSheetName
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function hasTableData(mixed $spreadsheet, string $table): bool
    {
        $rows = $this->extractTableData($spreadsheet, $table);

        return !empty($rows);
    }
}
