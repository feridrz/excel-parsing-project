<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Excel Import</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">

            @if(session('success'))
                <div class="alert alert-success mt-3">
                    {{ session('success') }}
                </div>
            @endif



            <div class="card shadow">
                <div class="card-header">
                    <h4>Upload Excel</h4>
                </div>
                <div class="card-body">
                    <form action="{{ url('/excel/upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" name="file" class="form-control" accept=".xlsx" required>
                        </div>
                        <button class="btn btn-primary" type="submit">Upload</button>
                    </form>


                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
