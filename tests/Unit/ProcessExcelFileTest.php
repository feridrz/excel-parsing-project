<?php

namespace Tests\Unit;

use App\Jobs\ProcessExcelChunk;
use App\Jobs\ProcessExcelFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ProcessExcelFileTest extends TestCase
{
    use RefreshDatabase;

    private $filePath;
    private $reportPath;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('app');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Date');

        for ($i = 2; $i <= 1501; $i++) {
            $sheet->setCellValue('A' . $i, $i - 1);
            $sheet->setCellValue('B' . $i, 'Test Name ' . ($i - 1));
            $sheet->setCellValue('C' . $i, date('d.m.Y'));
        }

        $this->filePath = storage_path('app/test_import.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($this->filePath);

        $this->reportPath = storage_path('app/test_report.txt');
        file_put_contents($this->reportPath, '');
    }

    public function tearDown(): void
    {
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }

        if (file_exists($this->reportPath)) {
            unlink($this->reportPath);
        }

        parent::tearDown();
    }

    public function test_process_excel_file_dispatches_chunks()
    {
        Queue::fake();

        $job = new ProcessExcelFile($this->filePath, $this->reportPath);
        $job->handle();

        Queue::assertPushed(ProcessExcelChunk::class, 2);
    }
}
