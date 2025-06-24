<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use App\Models\SerialNumber;
use App\Models\WarrantyClaim;
use App\Models\Product;
use App\Models\Customer;
use Carbon\Carbon;

class WarrantyTracking extends Component
{
    use WithPagination, Toast;

    public $search = '';
    public $statusFilter = 'all';
    public $activeTab = 'overview';

    // Claim form
    public $showClaimModal = false;
    public $claimSerialId = null;
    public $issueDescription = '';
    public $claimAmount = 0;
    public $resolutionType = 'repair';

    protected $rules = [
        'issueDescription' => 'required|string|min:10',
        'claimAmount' => 'nullable|numeric|min:0',
        'resolutionType' => 'required|in:repair,replace,refund,denied'
    ];

    public function render()
    {
        $stats = $this->getWarrantyStats();

        $warranties = SerialNumber::with(['product', 'customer', 'warehouse'])
            ->when($this->search, function ($q) {
                $q->where('serial_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('product', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('customer', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->when($this->statusFilter, function ($q) {
                switch ($this->statusFilter) {
                    case 'active':
                        $q->where('warranty_expires_at', '>', now())
                            ->where('status', 'sold');
                        break;
                    case 'expiring':
                        $q->where('warranty_expires_at', '>', now())
                            ->where('warranty_expires_at', '<=', now()->addDays(30))
                            ->where('status', 'sold');
                        break;
                    case 'expired':
                        $q->where('warranty_expires_at', '<', now())
                            ->where('status', 'sold');
                        break;
                }
            })
            ->whereNotNull('warranty_expires_at')
            ->orderBy('warranty_expires_at', 'asc')
            ->paginate(15);

        $recentClaims = WarrantyClaim::with(['customer', 'product'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.admin.warranty-tracking', [
            'warranties' => $warranties,
            'stats' => $stats,
            'recentClaims' => $recentClaims
        ]);
    }

    private function getWarrantyStats()
    {
        $today = now();

        return [
            'active_warranties' => SerialNumber::where('warranty_expires_at', '>', $today)
                ->where('status', 'sold')
                ->count(),
            'expiring_soon' => SerialNumber::where('warranty_expires_at', '>', $today)
                ->where('warranty_expires_at', '<=', $today->addDays(30))
                ->where('status', 'sold')
                ->count(),
            'expired_this_month' => SerialNumber::whereBetween(
                'warranty_expires_at',
                [$today->startOfMonth(), $today->endOfMonth()]
            )
                ->where('status', 'sold')
                ->count(),
            'claims_this_month' => WarrantyClaim::whereMonth('created_at', $today->month)
                ->whereYear('created_at', $today->year)
                ->count()
        ];
    }

    public function openClaimModal($serialId)
    {
        $this->claimSerialId = $serialId;
        $this->reset(['issueDescription', 'claimAmount', 'resolutionType']);
        $this->showClaimModal = true;
    }

    public function submitClaim()
    {
        $this->validate();

        $serial = SerialNumber::find($this->claimSerialId);

        WarrantyClaim::create([
            'customer_id' => $serial->sold_to_customer_id,
            'product_id' => $serial->product_id,
            'serial_number_id' => $serial->id,
            'purchase_date' => $serial->sold_at,
            'claim_date' => now(),
            'issue_description' => $this->issueDescription,
            'status' => 'pending',
            'resolution_type' => $this->resolutionType,
            'claim_amount' => $this->claimAmount,
            'handled_by' => auth()->id()
        ]);

        $this->success('Warranty claim created successfully!');
        $this->showClaimModal = false;
        $this->reset(['claimSerialId', 'issueDescription', 'claimAmount', 'resolutionType']);
    }
}
