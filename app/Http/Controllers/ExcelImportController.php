<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessExcelFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ExcelImportController extends Controller
{
    public function showUploadForm()
    {
        return view('excel.upload');
    }
    public function upload(Request $request)
    {

        $request->validate([
            'file' => 'required|file|mimes:xlsx'
        ]);

        $file = $request->file('file');
        $filename = uniqid('import_') . '.' . $file->getClientOriginalExtension();
        $storagePath = storage_path('app/imports');
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        $file->move($storagePath, $filename);
        $filePath = $storagePath . '/' . $filename;

        $reportFile = storage_path('app/result.txt');
        if (file_exists($reportFile)) {
            unlink($reportFile);
        }

        Redis::set('import_progress', 0);

        ProcessExcelFile::dispatch($filePath, $reportFile);

        return redirect()->back()->with('success', 'Import started');
    }


    public function progress()
    {
        $processed = Redis::get('import_progress') ?: 0;
        return response()->json(['processed' => (int)$processed]);
    }


}
