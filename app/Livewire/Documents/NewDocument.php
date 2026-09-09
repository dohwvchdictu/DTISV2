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
    }

    public function create()
    {
        try {
            $data = $this->validate([
                'control_no' => 'required',
                'source' => 'required|max:8',
                /** A charter transaction is classified by its charter procedure, so
                 *  the type/category selects are hidden and contribute nothing. */
                'category_id' => $this->isCharterTransaction() ? 'nullable' : 'required',
                'subject' => 'required|min:8|max:500',
                'user' => 'required',
                'is_arta' => 'nullable',
                'is_bundle' => 'nullable',
                'citizen_charter_id' => 'nullable'
            ], [], [
                /** Named as the form labels them, so the toast reads back to the
                 *  field the user has to go and fix */
                'control_no' => 'document control no.',
                'source' => 'document source',
                'category_id' => 'document category',
                'citizen_charter_id' => 'charter procedure',
            ]);
        } catch (ValidationException $e) {
            $this->alert('error', $this->validationSummary($e), [
                'position' => 'top-end',
                'timer' => 6000,
                'toast' => true
            ]);

            /** Rethrown so Livewire still records the errors and stops the save */
            throw $e;
        }

        if ($this->isCharterTransaction()) {
            $data['category_id'] = null;
        }

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

        $type = $data['is_bundle'] == '1' ? 'Bundle' : 'Document';

        $log = Log::create([
            'action_id' => Action::where('name', 'Created')->first()->id,
            'document_id' => $document->id,
            'user_id' => $this->user['id'],
            'office_id' => $this->office,
            'assigned_to' => null,
            'description' => $type . " is created. Preparing to print tracking form."
        ]);

        $this->reset('control_no', 'source', 'category_id', 'subject', 'citizen_charter_id');

        $this->alert('success', $type . ' successfully created!', [
            'position' => 'top-end',
            'timer' => 10000,
            'toast' => true
        ]);


        /** An uncategorised (charter) document belongs on the general list */
        if ($document->category_id === null) {
            return $this->redirect(MyDocuments::class);
        } elseif (in_array($document->category_id, $this->purchase_request_array)) {
            return $this->redirect(MyPurchaseRequests::class);
        } elseif (in_array($document->category_id, $this->payment_array)) {
            return $this->redirect(MyPayments::class);
        } else {
            return $this->redirect(MyDocuments::class);
        }
    }

    /**
     * The form carries no inline error text, so the toast has to say what is
     * actually wrong: the first failure in full, plus a count of the rest.
     */
    private function validationSummary(ValidationException $e): string
    {
        $messages = $e->validator->errors()->all();

        $first = $messages[0] ?? 'Please check the form and try again.';
        $remaining = count($messages) - 1;

        if ($remaining < 1) {
            return $first;
        }

        return sprintf('%s (+%d more %s)', $first, $remaining, $remaining === 1 ? 'field' : 'fields');
    }

    /**
     * A Citizen's Charter transaction is classified by the charter procedure it
     * falls under, so document type and category are hidden and left unset.
     */
    public function isCharterTransaction(): bool
    {
        return $this->showCitizenProcedure || $this->is_arta == '1';
    }

    public function updatedShowCitizenProcedure()
    {
        if ($this->showCitizenProcedure) {
            /** Drop anything already picked, so a switch to Yes cannot save a
             *  category the user can no longer see */
            $this->reset('category_id', 'subject_placeholder');
            $this->resetValidation('category_id');
        } else {
            $this->citizen_charter_id = null;
        }
    }

    /**
     * The subject guidance follows the chosen category: payments and purchase
     * requests need particular details spelled out, everything else takes the
     * general narrative.
     */
    public function updatedCategoryId()
    {
        if (! $this->category_id) {
            $this->reset('subject_placeholder');
            return;
        }

        $name = Category::where('id', $this->category_id)->value('name') ?? '';

        if (str_contains($name, 'Payment')) {
            $this->subject_placeholder = '1) Payee, 2) Particulars with Date and Venue, 3) P.O Number (if available),  4) Total Amount';
        } elseif (str_contains($name, 'Purchase')) {
            $this->subject_placeholder = '1) Description / Particulars, 2) Total Amount';
        } else {
            $this->subject_placeholder = 'Type your document subject and other details (Who, When & Where)';
        }
    }

    public function completeName()
    {
        return $this->user['firstName'] . ' ' . $this->user['lastName'] . ' ' . $this->user['suffix'];
    }


    public function render()
    {

        return view('livewire.documents.new-document', [
            /** Every category, unfiltered - the document type filter is gone */
            'categories' => Category::orderBy('name')->get(),
            'citizen_charters' => CitizenCharter::where('is_active', true)->get(),
            'control_no' => $this->control_no = 'DC' . $this->office . $this->user['id'] . Carbon::now()->format('Ymdhis')
        ]);
    }
}
