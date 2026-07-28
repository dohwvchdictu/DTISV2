<?php

namespace App\Livewire\Documents;

use App\Livewire\Inbox\MyDocuments;
use App\Livewire\Inbox\MyPayments;
use App\Livewire\Inbox\MyPurchaseRequests;
use App\Models\Action;
use App\Models\Category;
use App\Models\CitizenCharter;
use App\Models\Document;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Component;

class NewDocument extends Component
{
    use LivewireAlert;

    #[Title('New Document | Document Tracking Information System')]

    /** Constant */
    public $purchase_request_array = [];
    public $payment_array = [];
    public $selectedType;
    public $categories = [];
    public $subject_placeholder = 'Type your document subject and details...';

    public $showCitizenProcedure = false;

    public $control_no;
    public $source;
    public $category_id;
    public $subject;
    public $office;
    public $user = [];
    public $is_arta = false;
    public $is_bundle = false;
    public $citizen_charter_id = null;
    public $hello_world;

    public function mount()
    {
        /** User Information */
        $this->user = session('user');
        $this->office = $this->user['office']['id'];
        /** End User Information */

        $purchase_request_obj = Category::where('name', 'like', '%' . 'Purchase' . '%')->select('id')->get();
        foreach ($purchase_request_obj->toArray() as $value) {
            $this->purchase_request_array[] = $value['id'];
        }

        $payment_obj = Category::where('name', 'like', '%' . 'Payment' . '%')->select('id')->get();
        foreach ($payment_obj->toArray() as $value) {
            $this->payment_array[] = $value['id'];
        }

        /**
         * Seed the Document Type so the Category dropdown has options on first paint.
         *
         * $categories is only ever filled by updatedSelectedType(), so until a radio was
         * clicked the select rendered a single "No records found." option — there was no
         * category to choose and category_id could never satisfy its 'required' rule.
         */
        $this->selectedType = 'All';
        $this->updatedSelectedType();

        /**
         * Generated once per form, here rather than in render().
         *
         * render() used to assign $this->control_no from Carbon::now(), so every
         * re-render — including the one a failed validation causes — silently minted a
         * new control number. The one the user was reading was not the one being saved.
         */
        $this->control_no = $this->generateControlNo();
    }

    /** Control number for this form: DC + office + user + timestamp */
    private function generateControlNo(): string
    {
        return 'DC' . $this->office . $this->user['id'] . Carbon::now()->format('Ymdhis');
    }

    /**
     * Surface a refused save as a toast, listing every reason.
     *
     * The exception is caught rather than left to bubble so this can run: Livewire would
     * otherwise handle it internally and the refusal was completely silent — the spinner
     * ran, the request completed, and nothing said why. A four-character subject looked
     * identical to a broken save.
     *
     * setErrorBag() puts the messages back where Livewire expects them, so the per-field
     * @error output under each input keeps working; the toast just makes the refusal
     * impossible to miss without hunting for the field at fault.
     */
    private function alertValidationErrors(ValidationException $e): void
    {
        $messages = $e->validator->errors()->all();

        $this->setErrorBag($e->validator->errors());

        $this->alert('error', 'Document not saved', [
            'position' => 'top-end',
            'timer' => 10000,
            'toast' => true,
            'html' => '<ul class="text-start ps-4 list-disc">'
                . collect($messages)->map(fn ($message) => '<li>' . e($message) . '</li>')->implode('')
                . '</ul>',
        ]);
    }

    public function create()
    {
        try {
            $data = $this->validate([
                'control_no' => 'required',
                'source' => 'required|max:8',
                'category_id' => 'required',
                'subject' => 'required|min:8|max:500',
                'user' => 'required',
                'is_arta' => 'nullable',
                'is_bundle' => 'nullable',
                'citizen_charter_id' => 'nullable'
            ]);
        } catch (ValidationException $e) {
            $this->alertValidationErrors($e);

            return;
        }

        /** Resolved before the write, so a missing action row cannot leave a document with no log */
        $createdActionId = Action::firstWhere('name', 'Created')?->id;

        if (!$createdActionId) {
            $this->alert('error', 'The "Created" action is missing. Contact the system administrator.', [
                'position' => 'center',
                'toast' => true,
                'timer' => null,
                'showConfirmButton' => true,
                'confirmButtonText' => 'OK',
                'confirmButtonColor' => '#dc2626',
            ]);

            return;
        }

        $type = $data['is_bundle'] == '1' ? 'Bundle' : 'Document';

        /** Document and its creation log are one unit — never one without the other */
        $document = DB::transaction(function () use ($data, $type, $createdActionId) {
            $document = Document::create([
                'control_no' => $data['control_no'],
                'source' => $data['source'],
                'category_id' => $data['category_id'],
                'subject' => $data['subject'],
                'user_id' => $this->user['id'],
                'office_id' => $this->office,
                'is_arta' => $data['is_arta'],
                'is_bundle' => $data['is_bundle'],
                'citizen_charter_id' => $data['citizen_charter_id'],
                'status' => "Created",
            ]);

            Log::create([
                'action_id' => $createdActionId,
                'document_id' => $document->id,
                'user_id' => $this->user['id'],
                'office_id' => $this->office,
                'assigned_to' => null,
                'description' => $type . " is created. Preparing to print tracking form."
            ]);

            return $document;
        });

        $this->reset('control_no', 'source', 'category_id', 'subject', 'citizen_charter_id');

        $this->alert('success', $type . ' successfully created!', [
            'position' => 'top-end',
            'timer' => 10000,
            'toast' => true
        ]);


        if (in_array($document->category_id, $this->purchase_request_array)) {
            return $this->redirect(MyPurchaseRequests::class);
        } elseif (in_array($document->category_id, $this->payment_array)) {
            return $this->redirect(MyPayments::class);
        } else {
            return $this->redirect(MyDocuments::class);
        }
    }

    public function updatedShowCitizenProcedure()
    {
        if (!$this->showCitizenProcedure) {
            $this->citizen_charter_id = null;
        }
    }

    public function updatedSelectedType()
    {
        if ($this->selectedType == "All") {

            return [
                $this->categories = Category::whereNot(function ($query) {
                    $query->where('name', 'like', '%' . 'Purchase' . '%')
                        ->orWhere('name', 'like', '%' . 'Payment' . '%');
                })
                    ->orderBy('name')
                    ->get(),

                $this->subject_placeholder = 'Type your document subject and other details (Who, When & Where)'
            ];
        } else {

            if ($this->selectedType == "Payment") {
                $this->subject_placeholder = '1) Payee, 2) Particulars with Date and Venue, 3) P.O Number (if available),  4) Total Amount';
            } else {
                $this->subject_placeholder = '1) Description / Particulars, 2) Total Amount';
            }

            return [
                $this->categories = Category::where('name', 'like', '%' . $this->selectedType . '%')->orderBy('name')->get(),
            ];
        }
    }

    public function completeName()
    {
        return $this->user['firstName'] . ' ' . $this->user['lastName'] . ' ' . $this->user['suffix'];
    }


    public function render()
    {

        /**
         * control_no is deliberately NOT regenerated here — it is assigned once in
         * mount(). Doing it in render() meant every re-render replaced it with a fresh
         * timestamp, so a failed validation changed the control number under the user.
         */
        return view('livewire.documents.new-document', [
            'citizen_charters' => CitizenCharter::where('is_active', true)->get(),
        ]);
    }
}
