<?php

declare(strict_types=1);

/**
 * Derafu: Seed - From spreadsheets to databases seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsSeed\Schema\Target;

use Derafu\Seed\Schema\Column;
use Derafu\Seed\Schema\ForeignKey;
use Derafu\Seed\Schema\Index;
use Derafu\Seed\Schema\Schema;
use Derafu\Seed\Schema\Source\DoctrineDbalSchemaSource;
use Derafu\Seed\Schema\Table;
use Derafu\Seed\Schema\Target\DoctrineDbalSchemaTarget;
use Doctrine\DBAL\Schema\Schema as DoctrineSchema;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\DBAL\Types\StringType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DoctrineDbalSchemaTarget::class)]
#[CoversClass(DoctrineDbalSchemaSource::class)]
#[CoversClass(Column::class)]
#[CoversClass(ForeignKey::class)]
#[CoversClass(Index::class)]
#[CoversClass(Schema::class)]
#[CoversClass(Table::class)]
final class DoctrineDbalSchemaTargetTest extends TestCase
{
    public function testApplySchema(): void
    {
        // Load the original Doctrine DBAL schema.
        $originalDoctrineDbalSchema = require __DIR__ . '/../../../fixtures/doctrine-dbal-schema.php';

        // Convert to our schema and back to Doctrine DBAL.
        $schemaSource = new DoctrineDbalSchemaSource();
        $ourSchema = $schemaSource->extractSchema($originalDoctrineDbalSchema);

        $schemaTarget = new DoctrineDbalSchemaTarget();
        $resultDoctrineDbalSchema = $schemaTarget->applySchema($ourSchema);

        // Verify the result is a Doctrine Schema
        $this->assertInstanceOf(DoctrineSchema::class, $resultDoctrineDbalSchema);

        // Verify that all tables are present
        $expectedTables = [
            'party', 'item', 'tax_scheme', 'tax_category',
            'invoice', 'invoice_line', 'invoice_line_tax',
        ];

        foreach ($expectedTables as $tableName) {
            $this->assertTrue(
                $resultDoctrineDbalSchema->hasTable($tableName),
                "Schema should have table '{$tableName}'."
            );
        }

        // Test specific table structure (e.g., invoice)
        $invoiceTable = $resultDoctrineDbalSchema->getTable('invoice');

        // Check columns.
        $this->assertTrue(
            $invoiceTable->hasColumn('id'),
            "Invoice table should have 'id' column."
        );
        $this->assertTrue(
            $invoiceTable->hasColumn('customer_id'),
            "Invoice table should have 'customer_id' column."
        );
        $this->assertTrue(
            $invoiceTable->hasColumn('supplier_id'),
            "Invoice table should have 'supplier_id' column."
        );

        // Check primary key.
        $primaryKey = $invoiceTable->getPrimaryKey();
        $this->assertNotNull(
            $primaryKey,
            "Invoice table should have a primary key."
        );
        $this->assertSame(
            ['id'],
            $primaryKey->getColumns(),
            "Invoice primary key should be 'id'."
        );

        // Check foreign keys.
        $foreignKeys = $invoiceTable->getForeignKeys();
        $this->assertNotEmpty(
            $foreignKeys,
            "Invoice table should have foreign keys."
        );

        $hasPartyFk = false;
        foreach ($foreignKeys as $fk) {
            if ($fk->getForeignTableName() === 'party') {
                $hasPartyFk = true;
                break;
            }
        }
        $this->assertTrue(
            $hasPartyFk,
            "Invoice table should have a foreign key to party table."
        );

        // Check indexes.
        $indexes = $invoiceTable->getIndexes();
        $this->assertNotEmpty($indexes, "Invoice table should have indexes.");
    }

    public function testRoundTrip(): void
    {
        // Test roundtrip conversion: Doctrine DBAL -> Our Schema -> Doctrine DBAL.
        $originalDoctrineDbalSchema = require __DIR__ . '/../../../fixtures/doctrine-dbal-schema.php';

        // Original schema tables and columns.
        $originalTables = [];
        foreach ($originalDoctrineDbalSchema->getTables() as $table) {
            $tableName = $table->getName();
            $originalTables[$tableName] = [];

            foreach ($table->getColumns() as $column) {
                $originalTables[$tableName][] = $column->getName();
            }

            sort($originalTables[$tableName]);
        }

        // Convert to our schema and back.
        $schemaSource = new DoctrineDbalSchemaSource();
        $ourSchema = $schemaSource->extractSchema($originalDoctrineDbalSchema);

        $schemaTarget = new DoctrineDbalSchemaTarget();
        $resultDoctrineDbalSchema = $schemaTarget->applySchema($ourSchema);

        // Resulting schema tables and columns.
        $resultTables = [];
        foreach ($resultDoctrineDbalSchema->getTables() as $table) {
            $tableName = $table->getName();
            $resultTables[$tableName] = [];

            foreach ($table->getColumns() as $column) {
                $resultTables[$tableName][] = $column->getName();
            }

            sort($resultTables[$tableName]);
        }

        // Compare the structure.
        $this->assertSame(
            array_keys($originalTables),
            array_keys($resultTables),
            "Same tables should be present after roundtrip conversion."
        );

        foreach ($originalTables as $tableName => $columns) {
            $this->assertSame(
                $columns,
                $resultTables[$tableName],
                "Same columns should be present in table '{$tableName}' after roundtrip conversion."
            );
        }
    }

    public function testTypeMapping(): void
    {
        // Create a simple test schema with different column types.
        $originalDoctrineDbalSchema = require __DIR__ . '/../../../fixtures/doctrine-dbal-schema.php';

        // Extract our schema.
        $schemaSource = new DoctrineDbalSchemaSource();
        $ourSchema = $schemaSource->extractSchema($originalDoctrineDbalSchema);

        // Convert back to Doctrine DBAL.
        $schemaTarget = new DoctrineDbalSchemaTarget();
        $resultDoctrineDbalSchema = $schemaTarget->applySchema($ourSchema);

        // Check type mapping for some key columns.
        $this->assertInstanceOf(
            StringType::class,
            $resultDoctrineDbalSchema->getTable('party')->getColumn('name')->getType(),
            "String type should be correctly mapped"
        );

        $this->assertInstanceOf(
            DecimalType::class,
            $resultDoctrineDbalSchema->getTable('invoice')->getColumn('total_amount')->getType(),
            "Decimal type should be correctly mapped"
        );

        $this->assertInstanceOf(
            GuidType::class,
            $resultDoctrineDbalSchema->getTable('invoice')->getColumn('id')->getType(),
            "GUID type should be correctly mapped"
        );

        $this->assertInstanceOf(
            DateType::class,
            $resultDoctrineDbalSchema->getTable('invoice')->getColumn('issue_date')->getType(),
            "Date type should be correctly mapped"
        );
    }
}
