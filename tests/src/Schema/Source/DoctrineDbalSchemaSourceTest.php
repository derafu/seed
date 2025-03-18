<?php

declare(strict_types=1);

/**
 * Derafu: Seed - From spreadsheets to databases seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsSeed\Schema\Source;

use Derafu\Seed\Contract\SchemaInterface;
use Derafu\Seed\Schema\Column;
use Derafu\Seed\Schema\ForeignKey;
use Derafu\Seed\Schema\Index;
use Derafu\Seed\Schema\Schema;
use Derafu\Seed\Schema\Source\DoctrineDbalSchemaSource;
use Derafu\Seed\Schema\Table;
use Doctrine\DBAL\Schema\Schema as DoctrineDbalSchema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DoctrineDbalSchemaSource::class)]
#[CoversClass(Schema::class)]
#[CoversClass(Table::class)]
#[CoversClass(Column::class)]
#[CoversClass(ForeignKey::class)]
#[CoversClass(Index::class)]
final class DoctrineDbalSchemaSourceTest extends TestCase
{
    public function testExtractSchema(): void
    {
        // Load the Doctrine DBAL schema.
        $doctrineDbalSchema = require __DIR__ . '/../../../fixtures/doctrine-dbal-schema.php';

        // Make sure it's a valid Doctrine DBAL Schema.
        $this->assertInstanceOf(DoctrineDbalSchema::class, $doctrineDbalSchema);

        // Create the schema source.
        $schemaSource = new DoctrineDbalSchemaSource();

        // Execute the method we're testing.
        $schema = $schemaSource->extractSchema($doctrineDbalSchema);

        // Test that the result is an instance of our SchemaInterface.
        $this->assertInstanceOf(SchemaInterface::class, $schema);

        // Verify expected tables are present.
        $expectedTables = [
            'party', 'item', 'tax_scheme', 'tax_category',
            'invoice', 'invoice_line', 'invoice_line_tax',
        ];

        foreach ($expectedTables as $tableName) {
            $this->assertTrue($schema->hasTable($tableName), "Schema should have table '$tableName'");
        }

        // Check a specific table structure.
        $invoiceTable = $schema->getTable('invoice');
        $this->assertNotNull($invoiceTable);
        $this->assertTrue($invoiceTable->hasColumn('id'));
        $this->assertTrue($invoiceTable->hasColumn('customer_id'));
        $this->assertTrue($invoiceTable->hasColumn('supplier_id'));
        $this->assertTrue($invoiceTable->hasColumn('total_amount'));

        // Check foreign keys exist.
        $foreignKeys = $invoiceTable->getForeignKeys();
        $this->assertNotEmpty($foreignKeys);

        // Verify that at least one foreign key points to 'party' table.
        $partyFkFound = false;
        foreach ($foreignKeys as $fk) {
            if ($fk->getForeignTableName() === 'party') {
                $partyFkFound = true;
                break;
            }
        }
        $this->assertTrue(
            $partyFkFound,
            "Invoice should have a foreign key to the party table."
        );
    }
}
