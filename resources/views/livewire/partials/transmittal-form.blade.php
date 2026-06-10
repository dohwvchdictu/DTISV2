<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DTIS - Transmittal Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="icon" href="{!! asset('/img/doh.ico') !!}" />
    <style>
        @page { size: A4 portrait; margin: 8mm 10mm; }
        @media print { .container-fluid { padding: 0 4px; } }
    </style>

</head>

<body onload="window.print()" style="font-size: 10px;">
    <div class="container-fluid">
        <div class="row">
            <div class="col text-center">
                <img src="{{ asset('img/doh.png') }}" alt="DOH Logo"
                    style="opacity: .8; width:80px;height:80px; position:absolute; left:120px; top:5px; z-index:1">
                <p class="mt-3"> Republic of the Philippines <br> DEPARTMENT OF HEALTH </br> Western Visayas <br>
                    Center
                    for Health Development </p>
                <img src="{{ asset('img/bagongpilipinas.png') }}" alt="Bagong Pilipinas Logo"
                    style="opacity: .8; width:80px;height:80px; position:absolute; right:120px; top:5px; z-index:1">
            </div>
        </div>
        <hr>
        <div class="d-flex align-items-center mb-2 px-3">
            <div style="width:110px; flex-shrink:0;"></div>
            <h4 class="mb-0 flex-grow-1 text-center">DOCUMENT TRACKING FORM</h4>
            <div class="text-center" style="width:110px; flex-shrink:0;">
                {!! $qrCode !!}
                <div style="font-size:8px; margin-top:2px; font-weight:600; letter-spacing:1px;">SCAN TO RECEIVE</div>
            </div>
        </div>
        <table class="table table-auto table-bordered mx-auto" style="border:2px">
            <tbody>
                <tr>
                    <td>CONTROL NO</td>
                    <th colspan="2">{{ $document->control_no }}</th>
                    <td>DATE</td>
                    <th>{{ \Carbon\Carbon::parse($document->created_at)->format('m/d/Y h:i:s A') }}</th>
                </tr>
                <tr>
                    <td>SOURCE</td>
                    <th>{{ Str::title($document->source) }}</th>
                    <td>CATEGORY</td>
                    <th colspan="2">{{ $document->category->name }}</th>
                </tr>
                @if ($document->citizen_charter_id)
                    <tr>
                        <td>CITIZEN CHARTER</td>
                        <th colspan="4">{{ \App\Models\CitizenCharter::find($document->citizen_charter_id)->name }}
                        </th>
                    </tr>
                @endif
                <tr>
                    <td>ORIGIN</td>
                    <th colspan="4">{{ $office }}</th>
                </tr>
                <tr>
                    <td>DESTINATION</td>
                    <th colspan="4">{{ $destination }}</th>
                </tr>
                <tr>
                    <td>ENCODED BY</td>
                    <th colspan="4">{{ $user['lastName'] . ', ' . $user['firstName'] }}</th>
                </tr>
                <tr>
                    <th rowspan="2">SUBJECT</th>
                    <td rowspan="2" colspan="4" style="width:400px; font-size:12px">
                        {{ Str::limit($document->subject, 300) }}</td>
                </tr>

            </tbody>
        </table>
        <table class="table table-auto table-bordered mx-auto" style="border:2px">
            <thead class="text-center text-sm">
                <tr>
                    <th rowspan="2">DATE</th>
                    <th colspan="2">OFFICE</th>
                    <th rowspan="2">COMMENT / REMARKS</th>
                </tr>
                <tr>
                    <th>FROM</th>
                    <th>NEXT</th>
                </tr>
            </thead>
            <tbody>
                <tr style="height:60px;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr style="height:60px;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr style="height:60px;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr style="height:60px;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr style="height:60px;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr style="height:60px;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr style="height:60px;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="d-flex justify-content-between mb-2">
            <em>*In the event of loss, please return this document to its owner.</em>
            <span>DOH-CHD6-MSD-RECORDS-SOP-02-FORM4-Rev1</span>
        </div>
        <br>
        <div class="d-flex justify-content-start">
            <span>Page 1 of 1</span>
        </div>
        <hr>
        <div class="text-center text-sm">
            <small>Brgy. Bolong Oeste, Santa Barbara, Iloilo 5002 <svg xmlns="http://www.w3.org/2000/svg" width="24"
                    height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dot">
                    <circle cx="12.1" cy="12.1" r="1" />
                </svg> https://www.wv.doh.gov.ph <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="lucide lucide-dot">
                    <circle cx="12.1" cy="12.1" r="1" />
                </svg> records@dohwv.com <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="lucide lucide-dot">
                    <circle cx="12.1" cy="12.1" r="1" />
                </svg>(033) 500 - 1030</small>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
