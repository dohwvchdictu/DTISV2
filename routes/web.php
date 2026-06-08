<?php

use App\Http\Controllers\MiscController;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Documents\NewBundle;
use App\Livewire\Documents\NewDocument;
use App\Livewire\HomePage;
use App\Livewire\Inbox\MyBundles;
use App\Livewire\Inbox\MyDocuments;
use App\Livewire\Inbox\MyPayments;
use App\Livewire\Inbox\MyPurchaseRequests;
use App\Livewire\Partials\Logbook;
use App\Livewire\Partials\Navbar;
use App\Livewire\Report\DocumentStatus;
use App\Livewire\Report\Employees;
use App\Livewire\Report\ExternalDocuments;
use App\Livewire\Report\InternalDocuments;
use App\Livewire\Status\Closed;
use App\Livewire\Status\Endorsed;
use App\Livewire\Status\Forwarded;
use App\Livewire\Status\Incoming;
use App\Livewire\Status\Pending;
use App\Livewire\Views\DocumentDetail;
use App\Livewire\Views\IncomingDetail;
use App\Livewire\Views\PendingDetail;
use App\Livewire\Views\QrReceive;
use App\Livewire\Views\RoutingLogbook;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', LoginPage::class)->name('login');

Route::middleware(['jwt.auth'])->group(function () {
    /** Dashboard */
    Route::get('/dashboard', HomePage::class)->name('dashboard');

    /** Create Document */
    Route::get('/new-document', NewDocument::class);
    Route::get('/new-bundle', NewBundle::class);

    /** My Documents */
    Route::get('/my-documents', MyDocuments::class);
    Route::get('/my-bundles', MyBundles::class);
    Route::get('/my-purchase-requests', MyPurchaseRequests::class);
    Route::get('/my-payments', MyPayments::class);

    /** Status of Documents */
    Route::get('/status-incoming', Incoming::class);
    Route::get('/status-pending', Pending::class);
    Route::get('/status-endorsed', Endorsed::class);
    Route::get('/status-forwarded', Forwarded::class);
    Route::get('/status-closed', Closed::class);

    /** View Document */
    Route::get('/document/view/{control_no}', DocumentDetail::class)->name('document.view');
    Route::get('/document/incoming/{control_no}', IncomingDetail::class)->name('document.incoming');
    Route::get('/document/pending/{control_no}', PendingDetail::class)->name('document.pending');
    Route::get('/document/qr-receive/{control_no}', QrReceive::class)->name('document.qr-receive');
    Route::get('/routing-logbook', RoutingLogbook::class)->name('routing-logbook');

    /** Reports */
    Route::get('/report-status-of-documents', DocumentStatus::class);
    Route::get('/report-status-per-employee', Employees::class);
    Route::get('/report-status-of-external-documents', ExternalDocuments::class);
    Route::get('/report-status-of-internal-documents', InternalDocuments::class);

    /** Printing of Report*/
    Route::get('/print-document-status-report', [MiscController::class, 'printDocumentStatusReport'])->name('print.document.status');
    Route::get('/print-external-documents-report', [MiscController::class, 'printExternalDocumentsReport'])->name('print.external.documents');

    /** Printing of Transmittal */
    Route::get('/print-transmittal-form/{control_no}', [MiscController::class, 'printTransmittalForm'])->name('print.transmittal.form');
    Route::get('/inbox/generate-logbook', [MiscController::class, 'generateLogbook'])->name('inbox.generate-logbook');

    /** User Photo */
    Route::get('/employee/image/{filename}', [Navbar::class, 'getEmployeePhoto'])->name('employee.photo');

    /** Logout */
    Route::post('/logout', function () {
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

});
