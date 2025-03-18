<?php

declare(strict_types=1);

/**
 * Derafu: Seed - From spreadsheets to databases seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Seed\Data\Target;

use Derafu\Seed\Contract\DataTargetInterface;
use Derafu\Spreadsheet\Contract\SpreadsheetInterface;
use InvalidArgumentException;

/**
 * Inserts or updates data in a Derafu Spreadsheet.
 */
class SpreadsheetDataTarget implements DataTargetInterface
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
    public function applyTableData(
        mixed $spreadsheet,
        string $table,
        array $rows
    ): int {
        // Check if spreadsheet is a valid type.
        if (!$spreadsheet instanceof SpreadsheetInterface) {
            throw new InvalidArgumentException(
                '$spreadsheet must be an instance of SpreadsheetInterface.'
            );
        }

        // Check that we're not trying to modify the schema sheet.
        if ($table === $this->schemaSheetName) {
            throw new InvalidArgumentException(sprintf(
                'Cannot modify the schema sheet "%s" directly.',
                $this->schemaSheetName
            ));
        }

        // If no rows, nothing to do.
        if (empty($rows)) {
            return 0;
        }

        // Get or create the sheet.
        if ($spreadsheet->hasSheet($table)) {
            $sheet = $spreadsheet->getSheet($table);

            // Clear existing data rows (but keep the structure).
            $sheet->setRows([$sheet->getHeaderRow()]);
        } else {
            // Create a new sheet with associative data.
            $header = array_keys($rows[0]);
            $sheet = $spreadsheet->createSheet($table, [$header], true);
        }

        // Add all rows to the sheet.
        foreach ($rows as $row) {
            $sheet->addRow($row);
        }

        return count($rows);
    }
}
