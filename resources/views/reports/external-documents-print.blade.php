<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>External Documents Report</title>
    <style>
        /* Print Styles */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        
        .report-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .report-header h1 {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        
        .report-header .subtitle {
            font-size: 16px;
            margin: 5px 0;
        }
        
        .report-header .period {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }
        
        /* Cards Grid */
        .cards-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .card {
            display: table-cell;
            width: 25%;
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            vertical-align: top;
        }
        
        .card-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .card-value {
            font-size: 18px;
            font-weight: bold;
        }
        
        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        .data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .data-table td {
            font-size: 11px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        @media print {
            .print-button {
                display: none;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .report-container {
                max-width: none;
                margin: 0;
            }
            
            @page {
                size: A4;
                margin: 0.5in;
            }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Print Buttons -->
        <div class="print-button">
            <button class="btn" onclick="window.print()">Print Report</button>
            <button class="btn btn-secondary" onclick="window.history.back()">Go Back</button>
        </div>
        
        <!-- Report Header -->
        <div class="report-header">
            <h1>External Documents Report</h1>
            <div class="subtitle">Status Disaggregation by Office - External Requests</div>
            <div class="period">
                Report Period: {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}
            </div>
        </div>

        <!-- Overall Summary Cards -->
        @if(!empty($reportData['overall']))
        <div class="section">
            <div class="section-title">Overall Summary - External Documents</div>
            <div class="cards-grid">
                <div class="card">
                    <div class="card-title">For Receiving</div>
                    <div class="card-value">{{ number_format($reportData['overall']['incoming']) }}</div>
                </div>
                
                <div class="card">
                    <div class="card-title">Pending</div>
                    <div class="card-value">{{ number_format($reportData['overall']['pending']) }}</div>
                </div>
                
                <div class="card">
                    <div class="card-title">Processed</div>
                    <div class="card-value">{{ number_format($reportData['overall']['processed']) }}</div>
                </div>
                
                <div class="card">
                    <div class="card-title">Acted Upon</div>
                    <div class="card-value">
                        @php
                            $total = $reportData['overall']['incoming'] + $reportData['overall']['pending'] + $reportData['overall']['processed'];
                            $percentage = $reportData['overall']['processed'] && $total > 0 ? ($reportData['overall']['processed'] / $total) * 100 : 0;
                        @endphp
                        {{ number_format($percentage, 2) }}%
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Office-wise Table -->
        @if(!empty($reportData['offices']))
        <div class="section">
            <div class="section-title">External Documents by Office</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Office</th>
                        <th class="text-center">Incoming</th>
                        <th class="text-center">Pending</th>
                        <th class="text-center">Processed</th>
                        <th class="text-center">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['offices'] as $officeData)
                    <tr>
                        <td>{{ $officeData['office']['officeName'] }}</td>
                        <td class="text-center">{{ number_format($officeData['incoming']) }}</td>
                        <td class="text-center">{{ number_format($officeData['pending']) }}</td>
                        <td class="text-center">{{ number_format($officeData['processed']) }}</td>
                        <td class="text-center">{{ number_format($officeData['percentage'], 2) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</body>
</html>