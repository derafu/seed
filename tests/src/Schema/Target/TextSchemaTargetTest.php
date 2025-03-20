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
use Derafu\Seed\Schema\Source\DoctrineSchemaSource;
use Derafu\Seed\Schema\Table;
use Derafu\Seed\Schema\Target\TextSchemaTarget;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextSchemaTarget::class)]
#[CoversClass(DoctrineSchemaSource::class)]
#[CoversClass(Schema::class)]
#[CoversClass(Table::class)]
#[CoversClass(Column::class)]
#[CoversClass(ForeignKey::class)]
#[CoversClass(Index::class)]
final class TextSchemaTargetTest extends TestCase
{
    public function testApplySchema(): void
    {
        // Load the Doctrine DBAL schema.
        $doctrineSchema = require __DIR__ . '/../../../fixtures/doctrine-dbal-schema.php';

        // Create a schema from the Doctrine DBAL schema.
        $schemaSource = new DoctrineSchemaSource();
        $schema = $schemaSource->extractSchema($doctrineSchema);

        // Create the text schema target.
        $schemaTarget = new TextSchemaTarget();

        // Apply the schema to get the text representation.
        $textOutput = $schemaTarget->applySchema($schema);

        // Verify the output is a non-empty string.
        $this->assertIsString($textOutput);
        $this->assertNotEmpty($textOutput);

        // Verify that the text contains the expected table names.
        $expectedTables = [
            'party', 'item', 'tax_scheme', 'tax_category',
            'invoice', 'invoice_line', 'invoice_line_tax',
        ];

        foreach ($expectedTables as $tableName) {
            $this->assertStringContainsString(
                "TABLE: {$tableName}",
                $textOutput,
                "Output should contain table '{$tableName}'."
            );
        }

        // Verify that column details are included.
        $this->assertStringContainsString(
            "COLUMNS:",
            $textOutput,
            "Output should list columns."
        );
        $this->assertStringContainsString(
            "NOT NULL",
            $textOutput,
            "Output should show NOT NULL constraints."
        );

        // Verify that primary keys are included.
        $this->assertStringContainsString(
            "PRIMARY KEY:",
            $textOutput,
            "Output should list primary keys."
        );

        // Verify that foreign keys are included.
        $this->assertStringContainsString(
            "FOREIGN KEYS:",
            $textOutput,
            "Output should list foreign keys."
        );
        $this->assertStringContainsString(
            "ON DELETE:",
            $textOutput,
            "Output should include ON DELETE constraints."
        );
        $this->assertStringContainsString(
            "ON UPDATE:",
            $textOutput,
            "Output should include ON UPDATE constraints."
        );

        // Verify that indexes are included.
        $this->assertStringContainsString(
            "INDEXES:",
            $textOutput,
            "Output should list indexes."
        );
        $this->assertStringContainsString(
            "UNIQUE INDEX",
            $textOutput,
            "Output should show unique indexes."
        );

        // Optional: Display the output for manual inspection.
        // echo $textOutput;
    }
}
