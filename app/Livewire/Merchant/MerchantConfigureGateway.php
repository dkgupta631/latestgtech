<?php
namespace App\Livewire\Merchant;
use Livewire\Component;
use App\Models\GatewayAccount;
use App\Models\GatewayConfigurationMerchant;
use App\Models\Merchant;
use Livewire\WithDispatchesEvents;
use Livewire\WithPagination;

class MerchantConfigureGateway extends Component
{
    use WithPagination;
    public $search = '';        // Searching
    public $perPage = 10;       // Records per page
    protected $paginationTheme = 'bootstrap';
    public $gatewayAccountlist, $merchantlist;
    public function mount()
    {
        $this->gatewayAccountlist = GatewayAccount::where('status', '1')->get();
        $this->merchantlist = Merchant::where('status', '1')->get();
    }


    // FOR EDIT AND ADD RECORD FUNCTIONALITY CODE START
    public $configure_id,  $gateway_account_id ,$merchant_id;
    public $showModal = false; // To toggle the popup
    public $isEditMode = false; // To detect if editing or adding
    protected $rules = [
        'merchant_id' => 'required',
        'gateway_account_id' => 'required',
    ];

    /** Open modal for adding new record */
    public function openAddModal()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    /** Open modal for editing */
    public function editConfirmationFun($id)
    {
        $record = GatewayConfigurationMerchant::findOrFail($id);
        $this->configure_id = $record->id;
        $this->merchant_id = $record->merchant_id;
        $this->gateway_account_id = $record->gateway_account_id;

        $this->isEditMode = true;
        $this->showModal = true;
    }

    /** Save or update record */
    public function saveGatewayAccount()
    {
        $this->validate();
        // First, check for duplicates manually
        $exists = GatewayConfigurationMerchant::where('merchant_id', $this->merchant_id)
            ->where('gateway_account_id', $this->gateway_account_id)
            ->when($this->configure_id, function ($query) {
                // Exclude current record when editing
                $query->where('id', '!=', $this->configure_id);
            })
            ->exists();

        if ($exists) {
            $this->addError('gateway_account_id', __('messages.This configuration already exists!'));
            return;
        }

        GatewayConfigurationMerchant::updateOrCreate(
            ['id' => $this->configure_id],
            [
                'merchant_id' => $this->merchant_id,
                'gateway_account_id' => $this->gateway_account_id,
            ]
        );
        $this->getdata();
        $msg = $this->isEditMode  ? __('messages.Gateway Configuration updated successfully!') : __('messages.Gateway configured successfully!');
        $this->dispatch('toast', message: $msg, notify:'success' ); 
        $this->closeModal();
    }

    /** Close modal */
    public function closeModal()
    {
        $this->resetInputFields();
        $this->showModal = false;
    }

    /** Reset input fields */
    private function resetInputFields()
    {
        $this->configure_id = null;
        $this->merchant_id = '';
        $this->gateway_account_id = '';
    }
    // FOR EDIT AND ADD RECORD FUNCTIONALITY CODE END


    public function getdata(){
        return GatewayConfigurationMerchant::query()
            ->with(['merchant', 'gatewayAccount'])
            ->whereHas('merchant')      // skip configurations whose merchant no longer exists
            ->where(function ($q) {
                $q->where('gateway_account_id', 'like', "%{$this->search}%")
                  ->orWhere('merchant_id', 'like', "%{$this->search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
    }
    // FOR CHANGE STATUS CODE START
    public function toggleStatus($configureId)
    {
        $data = GatewayConfigurationMerchant::find($configureId);
        if ($data) {
            $data->status = !$data->status;
            $data->save();
            // Emit an event to trigger SweetAlert in frontend
            $this->dispatch('status-changed');
        }
    }
    // FOR CHANGE STATUS CODE END
    // FOR DELETE RECORD CODE START
    protected $listeners = ['callDeleteListner' => 'delete'];
    public function deleteConfirmationFun($id){
      
        $this->configure_id = $id;
        $this->dispatch('show-delete-confirmation');
    }
    public function delete(){
      
         $data= GatewayConfigurationMerchant::find($this->configure_id);
         $data->delete();
         $this->getdata();
         $this->dispatch('recordDeleted');
    }
    // FOR DELETE RECORD CODE END


    public function render()
    {
        return view('livewire.merchant.merchant-configure-gateway', [
            'records' => $this->getData(),
        ]);
    }

}
