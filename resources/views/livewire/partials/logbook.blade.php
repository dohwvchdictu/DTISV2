<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Electronic Document Logbook - {{ now()->format('F d, Y') }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            body {
                font-size: 10px;
                line-height: 1.3;
            }

            .print\:hidden {
                display: none !important;
            }

            .print\:shadow-none {
                box-shadow: none !important;
            }

            .print\:border-gray-400 {
                border-color: #9ca3af !important;
            }

            @page {
                margin: 0.25in;
                size: portrait;
            }

            .no-print {
                display: none !important;
            }

            /* Print-specific table styles */
            .print-table {
                width: 100% !important;
                table-layout: fixed !important;
                border-collapse: collapse !important;
            }

            .print-table th,
            .print-table td {
                border: 1px solid #000 !important;
                padding: 4px !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                hyphens: auto !important;
                font-size: 9px !important;
            }

            .print-table th {
                background-color: #f0f9f0 !important;
                font-weight: bold !important;
                text-align: center !important;
            }

            /* Column widths for print */
            .col-control { width: 17% !important; }
            .col-date { width: 11% !important; }
            .col-subject { width: 35% !important; }
            .col-office { width: 11% !important; }
            .col-received-date { width: 13% !important; }
            .col-received-by { width: 13% !important; }

            /* Header styling for print */
            .print-header {
                text-align: center !important;
                margin-bottom: 15px !important;
            }

            .print-header h1 {
                font-size: 12px !important;
                margin-bottom: 5px !important;
            }

            .print-header p {
                font-size: 10px !important;
                margin: 2px 0 !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="max-w-full px-4 mx-auto">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col text-center">
                <img src="{{ asset('img/doh.png') }}" alt="DOH Logo"
                    style="opacity: .8; width:80px;height:80px; position:absolute; left:120px; top:5px; z-index:1">
                <p class="mt-3"> Republic of the Philippines <br> DEPARTMENT OF HEALTH </br> Western Visayas <br> Center
                    for Health Development </p>
                <img src="{{ asset('img/bagongpilipinas.png') }}" alt="Bagong Pilipinas Logo"
                    style="opacity: .8; width:80px;height:80px; position:absolute; right:120px; top:5px; z-index:1">
            </div>
        </div>

        <div class="text-center mb-8 print-header">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Electronic Document Logbook</h1>
            <p class="text-gray-600">Generated on {{ now()->format('F d, Y h:i A') }}</p>
            <p class="text-gray-600">Total Documents: {{ $documentsArray->flatten(1)->count() }}</p>
        </div>

        @if($documentsArray->flatten(1)->count() > 0)
        <!-- Document Table -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden print:shadow-none print:border-gray-400">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 print-table">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-emerald-800 uppercase tracking-wider col-control">
                                Control No.
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-emerald-800 uppercase tracking-wider col-date">
                                Date Created
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-emerald-800 uppercase tracking-wider col-subject">
                                Subject
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-emerald-800 uppercase tracking-wider col-office">
                                Assigned Office
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-emerald-800 uppercase tracking-wider col-received-date">
                                Date Received
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-emerald-800 uppercase tracking-wider col-received-by">
                                Received By
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($documentsArray as $officeId => $documentsGroup)
                            @foreach($documentsGroup as $documentData)
                                <tr>
                                    <td class="px-3 py-2 text-sm font-medium text-emerald-800 col-control" style="word-break: break-word;">
                                        {{ $documentData['control_no'] }}
                                    </td>
                                    <td class="px-3 py-2 text-center text-sm text-gray-900 col-date" style="word-break: break-word;">
                                        {{ $documentData['created_at']->format('M d, Y') }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-900 col-subject" style="word-break: break-word;">
                                        <div class="font-medium text-xs">{{ $documentData['category'] }}</div>
                                        <div class="text-gray-600 text-xs mt-1" style="line-height: 1.2;">{{ \Illuminate\Support\Str::limit($documentData['subject'], 180) }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-center font-medium text-gray-900 col-office" style="word-break: break-word;">
                                        {{ $documentData['office_name'] }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-900 col-received-date" style="word-break: break-word;">
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-900 col-received-by" style="word-break: break-word;">
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Print and Navigation Buttons -->
        <div class="flex justify-center gap-4 mt-8 no-print">
            <button onclick="window.print()"
                class="py-2 px-6 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:bg-emerald-700">
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6,9 6,2 18,2 18,9" />
                    <path d="M6,18H4a2,2,0,0,1-2-2V11a2,2,0,0,1,2-2H20a2,2,0,0,1,2,2v5a2,2,0,0,1-2,2H18" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
                Print Logbook
            </button>
            <a href="{{ url()->previous() }}"
                class="py-2 px-6 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7" />
                    <path d="M19 12H5" />
                </svg>
                Go Back
            </a>
        </div>
        @else
        <div class="text-center py-12">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Documents Selected</h3>
            <p class="text-gray-500">Please select documents to generate the electronic logbook.</p>
            <a href="{{ url()->previous() }}"
                class="mt-4 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                <svg class="shrink-0 size-4 mr-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7" />
                    <path d="M19 12H5" />
                </svg>
                Go Back
            </a>
        </div>
        @endif
    </div>
</body>

</html>