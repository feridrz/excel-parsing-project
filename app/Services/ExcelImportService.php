<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redis;
use App\Jobs\ProcessExcelFile;
use Illuminate\Support\Facades\Log;

class ExcelImportService
{
    public function import(UploadedFile $file): void
    {
        try {
            $filename    = uniqid('import_') . '.' . $file->getClientOriginalExtension();
            $storagePath = storage_path('app/imports');
            if (! is_dir($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $file->move($storagePath, $filename);
            $filePath    = $storagePath . '/' . $filename;

            $reportFile  = storage_path('app/result.txt');
            if (file_exists($reportFile)) {
                unlink($reportFile);
            }
            Redis::set('import_progress', 0);

            ProcessExcelFile::dispatch($filePath, $reportFile);
        } catch (\Throwable $e) {
            Log::error('ExcelImportService failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
