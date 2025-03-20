<?php

declare(strict_types=1);

/**
 * Derafu: Seed - From spreadsheets to databases seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Seed\Command;

use Derafu\Seed\Contract\DatabaseManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to convert a spreadsheet file to a SQLite database.
 *
 * This command takes a spreadsheet file (XLSX, ODS, CSV, etc.) and converts it
 * to a SQLite database. The spreadsheet must have a specific structure:
 *
 *   - A sheet named "__schema" containing the database schema information.
 *   - Additional sheets containing the data for each table.
 *
 * Example usage:
 *
 *   php app/console.php derafu:seed data.xlsx data.sqlite
 *   php app/console.php derafu:seed data.xlsx --drop-tables
 *   php app/console.php derafu:seed data.xlsx --structure-only
 */
#[AsCommand(
    name: 'derafu:seed',
    description: 'Convert a spreadsheet to a SQLite database.'
)]
final class SeedCommand extends Command
{
    /**
     * Configure the command.
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                'source',
                InputArgument::REQUIRED,
                'Source spreadsheet file.'
            )
            ->addArgument(
                'target',
                InputArgument::OPTIONAL,
                'Target SQLite database file (default: source name with .sqlite extension).'
            )
            ->addOption(
                'drop-database',
                null,
                InputOption::VALUE_NONE,
                'Drop existing database before creating new one.'
            )
            ->addOption(
                'drop-tables',
                null,
                InputOption::VALUE_NONE,
                'Drop existing tables before creating new ones.'
            )
            ->addOption(
                'drop-data',
                null,
                InputOption::VALUE_NONE,
                'Drop existing data before creating new ones.'
            )
            ->addOption(
                'structure-only',
                null,
                InputOption::VALUE_NONE,
                'Create structure only (no data).'
            )
            ->addOption(
                'no-confirm',
                null,
                InputOption::VALUE_NONE,
                'Do not confirm the seeding process.'
            )
            ->setHelp(
                <<<'HELP'
The <info>derafu:seed</info> command converts a spreadsheet file to a SQLite database.

The spreadsheet must have a specific structure:

  - A sheet named "__schema" containing the database schema information.
  - Additional sheets containing the data for each table.

<info>Examples:</info>

  <comment># Basic usage (auto-detect format, output to invoice.sqlite)</comment>
  php %command.full_name% invoice.xlsx

  <comment># Specify input and output files</comment>
  php %command.full_name% invoice.xlsx database.sqlite

  <comment># Specify format explicitly</comment>
  php %command.full_name% invoice.csv database.sqlite --format=csv

  <comment># Drop existing tables if they exist</comment>
  php %command.full_name% invoice.xlsx --drop-tables

  <comment># Create structure only (no data)</comment>
  php %command.full_name% invoice.xlsx --structure-only

<info>Schema Format:</info>

The "__schema" sheet must contain the following columns:

  - type: Type of entity (table, column, index, foreign_key).
  - name: Name of the entity (table name or table.column format).
  - properties: Properties as a JSON object or associative array.
HELP
            );
    }

    /**
     * Constructor.
     *
     * @param DatabaseManagerInterface $databaseManager The database manager.
     * @param string|null $name The command name.
     */
    public function __construct(
        private readonly DatabaseManagerInterface $databaseManager,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     * @return int The command exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get source and target options.
        $source = $this->getSource($input);
        $target = $this->getTarget($input);

        // Show configuration.
        $io->section('Configuration of the seeding process');
        $headers = ['Database', 'Option', 'Type', 'Value'];
        $rows = [];
        foreach ($source as $key => $value) {
            $rows[] = [
                'Source',
                $key,
                get_debug_type($value),
                $this->formatValue($value),
            ];
        }
        foreach ($target as $key => $value) {
            $rows[] = [
                'Target',
                $key,
                get_debug_type($value),
                $this->formatValue($value),
            ];
        }
        $io->table($headers, $rows);

        // Confirm the seeding process.
        $confirm = $input->getOption('no-confirm') ?? false;
        if (!$confirm) {
            $io->section('Confirm the seeding process');
            $io->text('Are you sure you want to proceed?');
            if (!$io->confirm('Continue?', false)) {
                $io->error('Seeding process aborted.');
                return Command::FAILURE;
            }
        }

        // Connect to the source and target databases. Then load the source
        // spreadsheet into the target database.
        try {
            $sourceDatabase = $this->databaseManager->connect($source);
            $targetDatabase = $this->databaseManager->connect($target);
            $targetDatabase->load($sourceDatabase);
        } catch (Exception $e) {
            $io->error(sprintf(
                'Error during the seeding process: %s',
                $e->getMessage()
            ));
            return Command::FAILURE;
        }

        // Return success.
        $io->success('Seeding process completed successfully.');
        return Command::SUCCESS;
    }

    /**
     * Get the source database options.
     *
     * @param InputInterface $input The input interface.
     * @return array The source options.
     */
    private function getSource(InputInterface $input): array
    {
        $file = $input->getArgument('source');

        $options = [
            'file' => $file,
            'format' => pathinfo($file, PATHINFO_EXTENSION),
            'readOnly' => true,
            'createIfNotExists' => false,
        ];

        return $options;
    }

    /**
     * Get the target database options.
     *
     * @param InputInterface $input The input interface.
     * @return array The target options.
     */
    private function getTarget(InputInterface $input): array
    {
        $file = $input->getArgument('target');
        if (empty($file)) {
            $file = pathinfo($input->getArgument('source'), PATHINFO_FILENAME) . '.sqlite';
        }

        $options = [
            'file' => $file,
            'format' => pathinfo($file, PATHINFO_EXTENSION),
            'readOnly' => false,
            'createIfNotExists' => true,
            'dropDatabase' => $input->getOption('drop-database') ?? false,
            'dropTables' => $input->getOption('drop-tables') ?? false,
            'dropData' => $input->getOption('drop-data') ?? false,
            'structureOnly' => $input->getOption('structure-only') ?? false,
        ];

        if ($options['format'] === 'sqlite') {
            $options['doctrine'] = $options['file'];
        }

        return $options;
    }

    /**
     * Format the value.
     *
     * @param mixed $value The value to format.
     * @return string The formatted value.
     */
    private function formatValue(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
