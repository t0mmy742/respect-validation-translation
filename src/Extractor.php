<?php

declare(strict_types=1);

namespace t0mmy742\RespectValidationTranslation;

use FilesystemIterator;
use SplFileInfo;
use RuntimeException;

use function addcslashes;
use function basename;
use function date;
use function file_exists;
use function file_put_contents;
use function implode;
use function is_dir;
use function mkdir;

class Extractor
{
    private const RESPECT_VALIDATION_EXCEPTIONS_DIR = __DIR__ . '/../vendor/respect/validation/library/Exceptions';
    private const OUTPUT_FILENAME = 'respect-validation-translation';
    private const DEFAULT_OUTPUT_PATH = __DIR__ . '/../' . self::OUTPUT_FILENAME . '.pot';

    /** @var string[] */
    private array $inputFiles = [];
    /** @var array<string, array>{string, int} */
    private array $data = [];

    public function extract(?string $locale = null): void
    {
        $this->scanRespectValidationExceptionsDirectory();

        $fileExtractor = new NodeVisitor();
        foreach ($this->inputFiles as $inputFile) {
            $data = $fileExtractor->extractMessages($inputFile);
            foreach ($data as $message => $lines) {
                foreach ($lines as $line) {
                    $this->data[$message][] = [basename($inputFile), $line];
                }
            }
        }

        $this->saveToTemplateFile($this->getOutputPath($locale));
    }

    private function scanRespectValidationExceptionsDirectory(): void
    {
        if (is_dir(self::RESPECT_VALIDATION_EXCEPTIONS_DIR)) {
            $it = new FilesystemIterator(self::RESPECT_VALIDATION_EXCEPTIONS_DIR);
            foreach ($it as $file) {
                /** @var SplFileInfo $file */
                $this->inputFiles[] = $file->getPathname();
            }
        } else {
            throw new RuntimeException('Can\'t load Exceptions files');
        }
    }

    private function saveToTemplateFile(string $outputPath): void
    {
        $output = [];
        $output[] = '# Translation for Respect/Validation PHP library';
        $output[] = '#';
        $output[] = 'msgid ""';
        $output[] = 'msgstr ""';
        $output[] = '"POT-Creation-Date: ' . date('Y-m-d H:iO') . '\n"';
        $output[] = '"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\n"';
        $output[] = '"Last-Translator: FULL NAME <EMAIL@ADDRESS>\n"';
        $output[] = '"Language-Team: LANGUAGE <LL@li.org>\n"';
        $output[] = '"MIME-Version: 1.0\n"';
        $output[] = '"Content-Type: text/plain; charset=UTF-8\n"';
        $output[] = '"Content-Transfer-Encoding: 8bit\n"';
        $output[] = '';

        foreach ($this->data as $message => $filenamesLines) {
            $outputReference = '#:';
            foreach ($filenamesLines as $filenameLine) {
                $outputReference .= ' ' . $filenameLine[0] . ':' . $filenameLine[1];
            }
            $output[] = $outputReference;

            $message = addcslashes($message, '"\\');
            $output[] = 'msgid "' . $message . '"';
            $output[] = 'msgstr ""';
            $output[] = '';
        }

        $dataOutput = implode("\n", $output);

        file_put_contents($outputPath, $dataOutput);
    }

    private function getOutputPath(?string $locale): string
    {
        if ($locale === null) {
            $outputPath = self::DEFAULT_OUTPUT_PATH;
        } else {
            $outputDirectory = __DIR__ . '/locale/' . $locale . '/LC_MESSAGES/';
            if (!file_exists($outputDirectory)) {
                mkdir($outputDirectory, recursive: true);
            }

            $outputPath = $outputDirectory . self::OUTPUT_FILENAME . '.po';
        }

        return $outputPath;
    }
}
