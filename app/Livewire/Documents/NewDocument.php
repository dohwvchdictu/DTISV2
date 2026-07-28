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
    }

    public function create()
    {
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

        return view('livewire.documents.new-document', [
            'citizen_charters' => CitizenCharter::where('is_active', true)->get(),
            'control_no' => $this->control_no = 'DC' . $this->office . $this->user['id'] . Carbon::now()->format('Ymdhis')
        ]);
    }
}
