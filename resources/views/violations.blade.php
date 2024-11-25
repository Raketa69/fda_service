<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Violations and Recommendations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            Table: {{ $data['table'] }}
        </div>
        <div class="card-body">
            <!-- Violations Table -->
            <h5 class="card-title">Violations</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Determinant</th>
                        <th>Dependent</th>
                        <th>Type</th>
                        <th>Issue</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data['violations'] as $violation)
                        <tr>
                            <td>{{ $violation['determinant'] }}</td>
                            <td>{{ $violation['dependent'] }}</td>
                            <td>{{ implode(', ', $violation['type']) }}</td>
                            <td>{{ $violation['issue'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Recommendations Section -->
            <h5 class="card-title mt-4">Recommendations</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Action</th>
                        <th>Reason</th>
                        <th>New Table</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data['recommendations'] as $recommendation)
                        <tr>
                            <td>{{ $recommendation['action'] }}</td>
                            <td>{{ $recommendation['reason'] }}</td>
                            <td>{{ implode(', ', $recommendation['new_table']) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
