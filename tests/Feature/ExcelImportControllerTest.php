<?php

namespace Tests\Feature;

use App\Models\Row;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExcelImportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('app');
    }

    public function test_show_upload_form()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('excel.upload');
    }


    public function test_upload_validation_fails_with_invalid_file()
    {
        $file = UploadedFile::fake()->create('test.txt', 100);

        $response = $this->post('/excel/upload', [
            'file' => $file
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_progress_endpoint()
    {
        Redis::shouldReceive('get')->with('import_progress')->andReturn(42);

        $response = $this->get('/excel/progress');

        $response->assertStatus(200);
        $response->assertJson(['processed' => 42]);
    }

    public function test_show_data()
    {
        $date1 = now()->format('Y-m-d');
        $date2 = now()->addDay()->format('Y-m-d');
        Row::create([
            'excel_id' => 1,
            'name' => 'Test 1',
            'date' => $date1
        ]);

        Row::create([
            'excel_id' => 2,
            'name' => 'Test 2',
            'date' => $date1
        ]);

        Row::create([
            'excel_id' => 3,
            'name' => 'Test 3',
            'date' => $date2
        ]);

        $response = $this->get('/rows');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'last_page',
            'data'
        ]);
    }
}
