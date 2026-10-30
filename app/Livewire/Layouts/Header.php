<?php
namespace App\Livewire\Layouts;
use Livewire\Component;
use App\Models\TransactionNotification;

use Session;

class Header extends Component
{

    public $getNotification, $NotificationCount = 0, $NotificationData;
    // highest notification id this browser tab has already announced
    public $lastSeenId = 0;

    public function mount()
    {
        $this->getNotification();
        $this->lastSeenId = (int) $this->unreadQuery()->max('id');
    }

    // unread notifications for the logged-in role
    protected function unreadQuery()
    {
        $auth = Session::get('auth');
        if ($auth->role_name == 'Merchant') {
            return TransactionNotification::where('merchant_id', $auth->merchant_id)->where('readby_merchant', '0');
        }
        if ($auth->role_name == 'Agent') {
            return TransactionNotification::where('agent_id', $auth->agent_id)->where('readby_agent', '0');
        }
        return TransactionNotification::where('readby_admin', '0');
    }

    public function getNotification()
    {
        $this->NotificationCount = $this->unreadQuery()->count();
        // the dropdown shows the latest 20, the badge shows the full count
        $this->NotificationData = $this->unreadQuery()->orderByDesc('id')->limit(20)->get();
    }

    /**
     * Called every few seconds (wire:poll) and instantly on a Pusher event.
     * Announces new transaction notifications to the browser (sound + voice),
     * and skips re-rendering when nothing changed so an open dropdown stays open.
     */
    public function checkNew()
    {
        $new = $this->unreadQuery()->where('id', '>', $this->lastSeenId)->orderBy('id')->get();
        if ($new->isEmpty()) {
            $this->skipRender();
            return;
        }
        $this->lastSeenId = (int) $new->max('id');

        // resend-callback notices are admin actions, not new money movements
        $alerts = $new->reject(fn ($n) => str_starts_with((string) $n->notifiable_type, 'Notification send'))
            ->map(function ($n) {
                $d = json_decode($n->data, true) ?: [];
                return [
                    'type' => $d['type'] ?? $n->notifiable_type,
                    'msg' => $n->msg,
                    'transaction_id' => $d['transaction_id'] ?? '',
                    'amount' => $d['amount'] ?? '',
                    'currency' => $d['Currency'] ?? '',
                    'status' => $d['status'] ?? '',
                ];
            })->values();

        $this->getNotification();
        if ($alerts->isNotEmpty()) {
            $this->dispatch('mm-new-transactions', alerts: $alerts->all());
        }
    }

    public function markReadTransaction()
    {

        if(Session::get('auth')->role_name == 'Merchant'){
            TransactionNotification::where('merchant_id', Session::get('auth')->merchant_id)->update(['readby_merchant' => '1']);

        }elseif(Session::get('auth')->role_name == 'Agent'){
            TransactionNotification::where('agent_id', Session::get('auth')->agent_id)->update(['readby_agent' => '1']);
            
        }else{
             TransactionNotification::select('*')->update(['readby_admin' => '1']);  
        }
        TransactionNotification::where([
            'readby_merchant' => '1',
            'readby_agent' => '1',
            'readby_admin' => '1'
        ])->delete();
        $this->getNotification();
        // return back();
        $msg =  __('messages.Done!');
        $this->dispatch('toast', message: $msg, notify:'success' ); 
    }

    public function logout()
    {
        Session::flush();
        Session::forget('auth');
        $msg =  __('messages.Logout Successfully!');
        $this->dispatch('toast', message: $msg, notify:'success' ); 
        return $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.layouts.header');
    }
}
