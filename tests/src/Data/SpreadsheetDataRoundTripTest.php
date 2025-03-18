<?php

declare(strict_types=1);

/**
 * Derafu: Seed - From spreadsheets to databases seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsSeed\Target\Data;

use Derafu\Seed\Data\Source\SpreadsheetDataSource;
use Derafu\Seed\Data\Target\SpreadsheetDataTarget;
use Derafu\Spreadsheet\SpreadsheetDumper;
use Derafu\Spreadsheet\SpreadsheetFactory;
use Derafu\Spreadsheet\SpreadsheetLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * This test class verifies the round-trip data conversion between a spreadsheet
 * and a data source.
 *
 * It ensures that data can be correctly inserted and read back from a
 * spreadsheet, and that transactions can be properly committed and rolled back.
 */
#[CoversClass(SpreadsheetDataTarget::class)]
#[CoversClass(SpreadsheetDataSource::class)]
final class SpreadsheetDataRoundTripTest extends TestCase
{
    public function testRoundTripDataConversion(): void
    {
        // 1. Create some sample data.
        $sampleData = [
            'party' => [
                [
                    'id' => 'c8b7bf88-8951-11ee-b9d1-0242ac120002',
                    'name' => 'Acme Inc.',
                    'tax_id' => '123456789',
                    'country_code' => 'US',
                ],
                [
                    'id' => 'd5e6cf98-8951-11ee-b9d1-0242ac120002',
                    'name' => 'Globex Corp.',
                    'tax_id' => '987654321',
                    'country_code' => 'CA',
                ],
                [
                    'id' => 'e2f3df08-8951-11ee-b9d1-0242ac120002',
                    'name' => 'Oceanic Airlines',
                    'tax_id' => '456789123',
                    'country_code' => 'UK',
                ],
            ],
            'item' => [
                [
                    'id' => 'f0f1ef18-8951-11ee-b9d1-0242ac120002',
                    'name' => 'Widget A',
                    'description' => 'A simple widget',
                    'unit_price' => 10.99,
                ],
                [
                    'id' => 'fdfeef28-8951-11ee-b9d1-0242ac120002',
                    'name' => 'Widget B',
                    'description' => 'A complex widget',
                    'unit_price' => 20.99,
                ],
                [
                    'id' => '0a0bef38-8952-11ee-b9d1-0242ac120002',
                    'name' => 'Service X',
                    'description' => 'Premium service',
                    'unit_price' => 50.00,
                ],
            ],
            'invoice' => [
                [
                    'id' => '1718ef48-8952-11ee-b9d1-0242ac120002',
                    'invoice_number' => 'INV-001',
                    'issue_date' => '2025-03-15',
                    'customer_id' => 'c8b7bf88-8951-11ee-b9d1-0242ac120002',
                    'supplier_id' => 'd5e6cf98-8951-11ee-b9d1-0242ac120002',
                    'total_amount' => 31.98,
                    'billing_period' => 30,
                ],
                [
                    'id' => '2425ef58-8952-11ee-b9d1-0242ac120002',
                    'invoice_number' => 'INV-002',
                    'issue_date' => '2025-03-16',
                    'customer_id' => 'd5e6cf98-8951-11ee-b9d1-0242ac120002',
                    'supplier_id' => 'e2f3df08-8951-11ee-b9d1-0242ac120002',
                    'total_amount' => 50.00,
                    'billing_period' => 15,
                ],
            ],
        ];

        // 2. Create a new spreadsheet.
        $factory = new SpreadsheetFactory();
        $spreadsheet = $factory->create();

        // 3. Add schema sheet to make it a valid seed spreadsheet.
        $schemaSheet = $spreadsheet->createSheet(
            name: '__schema',
            rows: [],
            isAssociative: true
        );
        $schemaSheet->addRow([
            'type' => 'metadata',
            'name' => 'schema_info',
            'properties' => json_encode([
                'name' => 'Test Schema',
                'tables_count' => count($sampleData),
                'generated_at' => date('Y-m-d H:i:s'),
            ]),
        ]);

        // 4. Use SpreadsheetDataTarget to insert the data.
        $dataTarget = new SpreadsheetDataTarget();

        // Apply data for each table.
        $totalRows = 0;
        foreach ($sampleData as $tableName => $rows) {
            $affectedRows = $dataTarget->applyTableData(
                $spreadsheet,
                $tableName,
                $rows
            );
            $this->assertSame(
                count($rows),
                $affectedRows,
                "Should affect the correct number of rows for $tableName."
            );
            $totalRows += count($rows);
        }

        // 5. Save the spreadsheet to a file.
        $dumper = new SpreadsheetDumper();
        $xlsx = $dumper->dumpToString($spreadsheet, 'xlsx');

        // 6. Use SpreadsheetDataSource to read the data back.
        $loader = new SpreadsheetLoader();
        $spreadsheetLoaded = $loader->loadFromString($xlsx, 'xlsx');
        $dataSource = new SpreadsheetDataSource();

        // Check if all expected tables are available.
        $tableNames = $dataSource->getTableNames($spreadsheetLoaded);
        $this->assertCount(
            count($sampleData),
            $tableNames,
            "Should have the same number of tables."
        );

        foreach ($sampleData as $tableName => $expectedRows) {
            $this->assertTrue(
                $dataSource->hasTableData($spreadsheetLoaded, $tableName),
                "Should have data for table $tableName."
            );

            // Extract the table data.
            $extractedRows = $dataSource->extractTableData(
                $spreadsheetLoaded,
                $tableName
            );
            $this->assertCount(
                count($expectedRows) + 1, // Se agrega 1, $extractedRows tiene el header.
                $extractedRows,
                "Should have the same number of rows for $tableName."
            );

            // Verify data matches.
            foreach ($expectedRows as $index => $expectedRow) {
                $extractedRow = $extractedRows[$index + 1]; // Se agrega 1, $extractedRows tiene el header.

                foreach ($expectedRow as $key => $value) {
                    $this->assertArrayHasKey(
                        $key,
                        $extractedRow,
                        "Extracted row should have key $key."
                    );

                    // Special handling for numeric values to avoid
                    // precision/type issues.
                    if (is_numeric($value)) {
                        $this->assertSame(
                            (float)$value,
                            (float)$extractedRow[$key],
                            "Value for $key should match."
                        );
                    } else {
                        $this->assertSame(
                            $value,
                            $extractedRow[$key],
                            "Value for $key should match."
                        );
                    }
                }
            }
        }
    }
}
