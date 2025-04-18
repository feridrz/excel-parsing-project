<?php

namespace App\Jobs;

use App\Events\RowImported;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Row;
use Illuminate\Support\Facades\Redis;

class ProcessExcelChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $chunk;
    protected string $reportFile;

    public function __construct(array $chunk, string $reportFile)
    {
        $this->chunk = $chunk;
        $this->reportFile = $reportFile;
    }

    public function handle()
    {
        foreach ($this->chunk as $index => $row) {

            $line = $index + 2;
            $errors = [];

            [$id, $name, $date] = $row;

            //Validate rows
            if (!is_numeric($id) || (int)$id < 0) {
                $errors[] = 'Invalid id';
            }
            if (!preg_match("/^[A-Za-z '\-]+$/", $name)) {
                $errors[] = 'Invalid name';
            }
            $d = \DateTime::createFromFormat('d.m.Y', $date);
            if (!$d || $d->format('d.m.Y') !== $date) {
                $errors[] = 'Invalid date';
            }
            if (empty($errors) && Row::where('excel_id', $id)->exists()) {
                $errors[] = 'Duplicate id';
            }

            if (!empty($errors)) {
                file_put_contents(
                    $this->reportFile,
                    "$line - " . implode(', ', $errors) . PHP_EOL,
                    FILE_APPEND
                );
                continue;
            }

            $rowModel = Row::create([
                'excel_id' => $id,
                'name'     => $name,
                'date'     => $d->format('Y-m-d'),
            ]);

            broadcast(new RowImported($rowModel));
            Redis::incr('import_progress');

        }
    }
}
