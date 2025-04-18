<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use App\Jobs\ProcessExcelChunk;

class ProcessExcelFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected string $reportFile;

    public function __construct(string $filePath, string $reportFile)
    {
        $this->filePath = $filePath;
        $this->reportFile = $reportFile;
    }

    public function handle()
    {
        ini_set('memory_limit', '256M');
        $reader = IOFactory::createReaderForFile($this->filePath);
        $reader->setReadDataOnly(true);

        $chunkSize = 1000;
        $startRow = 2; // skip header of excell file

        while (true) {
            $filter = new class($startRow, $chunkSize) implements IReadFilter {
                private int $startRow;
                private int $endRow;

                public function __construct(int $startRow, int $chunkSize)
                {
                    $this->startRow = $startRow;
                    $this->endRow = $startRow + $chunkSize - 1;
                }

                public function readCell($column, $row, $worksheetName = ''): bool
                {
                    return $row >= $this->startRow && $row <= $this->endRow;
                }
            };

            $reader->setReadFilter($filter);
            $spreadsheet = $reader->load($this->filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // remove empty rows
            $rows = array_filter($rows, fn($r) => array_filter($r));
            $rows = array_values($rows);

            if (empty($rows)) {
                break;
            }
            ProcessExcelChunk::dispatch($rows, $this->reportFile)->onQueue('imports');

            if (count($rows) < $chunkSize) {
                break;
            }

            $startRow += $chunkSize;
        }
    }
}
