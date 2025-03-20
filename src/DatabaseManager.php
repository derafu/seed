<?php

declare(strict_types=1);

/**
 * Derafu: ETL - From spreadsheets to databases seamlessly.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\ETL;

use Derafu\ETL\Contract\DatabaseInterface;
use Derafu\ETL\Contract\DatabaseManagerInterface;
use Derafu\ETL\Database\DoctrineDatabase;
use Derafu\ETL\Database\PdoDatabase;
use Derafu\ETL\Database\SpreadsheetDatabase;
use Derafu\Spreadsheet\Contract\SpreadsheetFactoryInterface;
use Derafu\Spreadsheet\SpreadsheetFactory;
use InvalidArgumentException;

/**
 * Database manager.
 *
 * This class is responsible for managing the database connection to a PDO
 * connection or a spreadsheet. Last one is the default, and can be a file or a
 * string of data.
 */
final class DatabaseManager implements DatabaseManagerInterface
{
    /**
     * The spreadsheet factory.
     *
     * @var SpreadsheetFactoryInterface
     */
    private SpreadsheetFactoryInterface $spreadsheetFactory;

    /**
     * Constructor.
     *
     * @param SpreadsheetFactoryInterface $spreadsheetFactory The spreadsheet factory.
     */
    public function __construct(
        ?SpreadsheetFactoryInterface $spreadsheetFactory = null
    ) {
        $this->spreadsheetFactory = $spreadsheetFactory ?? new SpreadsheetFactory();
    }

    /**
     * Connect to a database.
     *
     * The options can be:
     *
     *   - The database connection options, that can be:
     *     - pdo: The PDO connection to connect to.
     *     - file: The path to the file to connect to as a spreadsheet.
     *     - data: The data to connect to as a spreadsheet.
     *     - spreadsheet: The spreadsheet to connect to.
     *   - The database options, that can be:
     *     - format: The format of the spreadsheet.
     *     - readOnly: Whether the database is read-only.
     *     - createIfNotExists: Whether to create the database if it does not exist.
     *   - The drop options, that can be:
     *     - dropDatabase: Whether to drop the database before creating it.
     *     - dropTables: Whether to drop the tables before creating them.
     *     - dropData: Whether to drop the data before populating the database.
     *
     * If no options are provided, a new spreadsheet will be created with the
     * default options:
     *
     *   - format: null
     *   - readOnly: false
     *   - createIfNotExists: false
     *   - dropDatabase: false
     *   - dropTables: false
     *   - dropData: false
     *
     * @param array $options The options for the database (not only connection).
     * @return DatabaseInterface The database representation.
     */
    public function connect(array $options = []): DatabaseInterface
    {
        $options = $this->resolveOptions($options);

        if (!empty($options['doctrine'])) {
            return $this->connectToDoctrine($options);
        }

        if (!empty($options['pdo'])) {
            return $this->connectToPdo($options);
        }

        if (!empty($options['file'])) {
            return $this->connectToFile($options);
        }

        if (!empty($options['data'])) {
            return $this->connectToData($options);
        }

        return $this->connectToSpreadsheet($options);
    }

    /**
     * Resolve the options.
     *
     * @param array $options The options for the database (not only connection).
     * @return array The resolved options.
     */
    private function resolveOptions(array $options): array
    {
        $options = array_merge([
            // Connection options.
            'pdo' => null,
            'file' => null,
            'data' => null,
            'spreadsheet' => null,

            // Database options.
            'format' => null,
            'readOnly' => false,
            'createIfNotExists' => false,

            // Drop options.
            'dropDatabase' => false,
            'dropTables' => false,
            'dropData' => false,
        ], $options);

        return $options;
    }

    /**
     * Connect to a Doctrine connection.
     *
     * @param array $options The options for the database (not only connection).
     * @return DatabaseInterface The database representation.
     */
    private function connectToDoctrine(array $options): DatabaseInterface
    {
        $doctrine = $options['doctrine'];

        return new DoctrineDatabase($doctrine, $options);
    }

    /**
     * Connect to a PDO connection.
     *
     * @param array $options The options for the database (not only connection).
     * @return DatabaseInterface The database representation.
     */
    private function connectToPdo(array $options): DatabaseInterface
    {
        $pdo = $options['pdo'];

        return new PdoDatabase($pdo, $options);
    }

    /**
     * Connect to a file.
     *
     * The file must be valid for the spreadsheet format handler.
     *
     * @param array $options The options for the database (not only connection).
     * @return DatabaseInterface The database representation.
     */
    private function connectToFile(array $options): DatabaseInterface
    {
        $file = $options['file'];
        $format = $options['format'] ?? pathinfo($file, PATHINFO_EXTENSION);

        $this->validateFile($file, $options);

        $handler = $this->spreadsheetFactory->createFormatHandler($file, $format);

        $options['spreadsheet'] = $handler->loadFromFile($file);

        return $this->connectToSpreadsheet($options);
    }

    /**
     * Connect to data.
     *
     * The data must be valid for the spreadsheet format handler. And the
     * format must be provided in the $options['format'] key.
     *
     * @param array $options The options for the database (not only connection).
     * @return DatabaseInterface The database representation.
     */
    private function connectToData(array $options): DatabaseInterface
    {
        $data = $options['data'];
        $format = $options['format']
            ?? throw new InvalidArgumentException(
                'Format is required for data connection.'
            )
        ;

        $handler = $this->spreadsheetFactory->createFormatHandler(
            'dummy.' . $format,
            $format
        );

        $options['spreadsheet'] = $handler->loadFromString($data);

        return $this->connectToSpreadsheet($options);
    }

    /**
     * Connect to a spreadsheet.
     *
     * @param array $options The options for the database (not only connection).
     * @return DatabaseInterface The database representation.
     */
    private function connectToSpreadsheet(array $options): DatabaseInterface
    {
        $spreadsheet = $options['spreadsheet']
            ?? $this->spreadsheetFactory->create()
        ;

        unset($options['spreadsheet']);

        return new SpreadsheetDatabase($spreadsheet, $options);
    }

    /**
     * Validate the file.
     *
     * If the file does not exist, it will be created if the
     * $options['createIfNotExists'] option is true.
     *
     * If the file exists, it will be checked if it is readable and writable.
     *
     * @param string $file The file to validate.
     * @param array $options The options for the database (not only connection).
     */
    private function validateFile(string $file, array $options): void
    {
        // Check file exists, create it if allowed or throw an error.
        if (!file_exists($file)) {
            // If the file does not exist and creation is disabled, throw an error.
            if (!$options['createIfNotExists']) {
                throw new InvalidArgumentException(sprintf(
                    'File does not exist and creation is disabled for this file: %s',
                    $file
                ));
            }

            // Check target directory exists or can be created.
            $directory = dirname($file);
            if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Target directory does not exist and cannot be created: %s',
                    $file,
                ));
            }

            // Check if the directory is writable.
            if (!is_writable($directory)) {
                throw new InvalidArgumentException(sprintf(
                    'Directory %s is not writable for creating an empty file: %s',
                    $directory,
                    basename($file),
                ));
            }

            // Create an empty file.
            file_put_contents($file, '');
        }

        // Check file is readable.
        if (!is_readable($file)) {
            throw new InvalidArgumentException(sprintf(
                'File is not readable: %s',
                $file
            ));
        }

        // Check file is writable if database is not read-only.
        if (!$options['readOnly'] && !is_writable($file)) {
            throw new InvalidArgumentException(sprintf(
                'File is not writable: %s',
                $file
            ));
        }
    }
}
