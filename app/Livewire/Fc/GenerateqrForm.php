<?php
namespace App\Livewire\Fc;
use Livewire\Component;
use Livewire\Attributes\Rule;
use App\Models\Qrgenerater;
use Brian2694\Toastr\Facades\Toastr;

class GenerateqrForm extends Component
{
     #[Rule('required')]
    public $customer_name, $Currency='THB', $amount, $invoice_number;
    public $url;
    public function saveQRdata()
    {
        $validated = $this->validate();
        if($this->Currency=='USDT'){
                // http://127.0.0.1:8000/m2p/payintest?amount=1000&Currency=THB&merchant_code=testmerchant005
                $this->url = url('/m2p/payintest?amount='.$this->amount.'&Currency=USD&merchant_code=FCmerchant001');
        }else{
                // $url = url('/fc/s2pdeposit/'.base64_encode($amount).'/'.base64_encode($invoice_number).'/'.base64_encode($customer_name));
                 $this->url = url('/fc/r2pdeposit/'.base64_encode($this->amount).'/'.base64_encode($this->invoice_number).'/'.base64_encode($this->customer_name));
        }

        $record = Qrgenerater::create([
            'customer_name' => $this->customer_name,
            'amount' => $this->amount,
            'invoice_number' => $this->invoice_number,
            'qr_img_url' =>  $this->url,
        ]);

        $msg = 'QR Generated Successfully!';
        $this->dispatch('toast', message: $msg, notify: 'success'); 
        return $this->redirect('/showQR/' . base64_encode($record->id), navigate: true);

    }

    public function render()
    {
        $title =   __('PAYMENT GATEWAY - QR');
        return view('livewire.fc.generateqr-form')->layout('components.layouts.master')->title($title);
    }
}
