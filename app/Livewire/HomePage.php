<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Document;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

class HomePage extends Component
{
    #[Title('Dashboard | Document Tracking Information System')]

    /** Constants */
    public $user = [];
    public $office;
    public $purchaseRequests_array = [];
    public $payments_array = [];
    public $categories_array = [];

    /** Status */
    public $incomings = 0;
    public $pendings = 0;
    public $processed = 0;
    public $percentage;

    /** Type */
    public $documents = 0;
    public $purchaseOrders = 0;
    public $payments = 0;
    public $bundles = 0;

    /** Filter Date Variables */
    public $startDate;
    public $endDate;

    public function mount()
    {
        /** User Information */
        $this->user = session('user', []);
        
        // Check if user has office information
        if (!isset($this->user['office']['id'])) {
            // Handle missing office data gracefully
            $this->office = null;
            $this->alert('error', 'User office information not found. Please login again.');
            return;
        }
        
        $this->office = $this->user['office']['id'];
        /** End User Information */

        /** Filter Records last 30 Days */
        $this->startDate = Carbon::now()->subMonth(1)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');

        /** Purchase request & Payments */
        $this->categories_array = Category::where(function ($query) {
            $query->where('name', 'like', '%' . 'Payment' . '%')
                ->orWhere('name', 'like', '%' . 'Purchase' . '%');
        })->pluck('id')->toArray();

        /** Type of Documents */
        $purchaseRequests_obj = Category::where('name', 'like', '%' . 'Purchase Request' . '%')->select('id')->get();
        foreach ($purchaseRequests_obj->toArray() as $value) {
            $this->purchaseRequests_array[] = $value['id'];
        }

        $payments_obj = Category::where('name', 'like', '%' . 'Payment' . '%')->select('id')->get();
        foreach ($payments_obj->toArray() as $value) {
            $this->payments_array[] = $value['id'];
        }
    }

    public function render()
    {
        /** Status of Documents */
        $this->incomings = Document::where('assigned_to', $this->office)->whereNull('bundle_id')->whereIn('status', ['For Receiving', 'Returned'])
            ->when($this->startDate, function ($query) {
                $query->whereBetween('created_at', [Carbon::parse($this->startDate), Carbon::parse($this->endDate)->addDay()]);
            })->count();
        $this->pendings = Document::where('assigned_to', $this->office)->whereNull('bundle_id')->whereIn('status', ['On Process', 'Endorsed'])->when($this->startDate, function ($query) {
            $query->whereBetween('created_at', [Carbon::parse($this->startDate), Carbon::parse($this->endDate)->addDay()]);
        })->count();
        $this->processed = Document::whereHas('logs', function ($query) {
            $query->where('assigned_to', $this->office)->whereIn('action_id', [3, 5]);
        })->when($this->startDate, function ($query) {
            $query->whereBetween('created_at', [Carbon::parse($this->startDate), Carbon::parse($this->endDate)->addDay()]);
        })->count();

        $total = $this->incomings + $this->pendings + $this->processed;
        $this->percentage = $this->processed ? ($this->processed / $total) * 100 : 0;

        /** Documents Disaggregation */
        $this->documents = Document::where('office_id', $this->office)
            ->when($this->categories_array, function ($query) {
                $query->whereNotIn('category_id', $this->categories_array);
            })
            ->when($this->startDate, function ($query) {
                $query->whereBetween('created_at', [Carbon::parse($this->startDate), Carbon::parse($this->endDate)->addDay()]);
            })->count();
        $this->purchaseOrders = Document::where('office_id', $this->office)
            ->when($this->purchaseRequests_array, function ($query) {
                $query->whereIn('category_id', $this->purchaseRequests_array);
            })
            ->when($this->startDate, function ($query) {
                $query->whereBetween('created_at', [Carbon::parse($this->startDate), Carbon::parse($this->endDate)->addDay()]);
            })
            ->count();
        $this->payments = Document::where('office_id', $this->office)
            ->when($this->payments_array, function ($query) {
                $query->whereIn('category_id', $this->payments_array);
            })
            ->when($this->startDate, function ($query) {
                $query->whereBetween('created_at', [Carbon::parse($this->startDate), Carbon::parse($this->endDate)->addDay()]);
            })
            ->get()->count();
        $this->bundles = Document::where('office_id', $this->office)->where('is_bundle', 1)
            ->when($this->startDate, function ($query) {
                $query->whereBetween('created_at', [Carbon::parse($this->startDate), Carbon::parse($this->endDate)->addDay()]);
            })->count();

        return view('livewire.home-page');
    }
}
