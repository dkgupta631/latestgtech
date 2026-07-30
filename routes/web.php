<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AJAXController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PostController;

use App\Http\Controllers\RichPayController;
use App\Http\Controllers\IpintPaymentController;
use App\Http\Controllers\XprizoPaymentController;
use App\Http\Controllers\QorePaymentController;

use App\Livewire\Auth\LoginPage;
use App\Livewire\DashboardPage;
use App\Livewire\ProfilePage;
use App\Livewire\DepositTransactionList;
use App\Livewire\WithdrawTransactionList;
use App\Livewire\Fc\GenerateqrForm;
use App\Livewire\Fc\ShowQR;
use App\Livewire\Fc\QrcodeList;
use App\Livewire\Fc\DepositFormRichpay;
use App\Livewire\PaymentForm\R2p\RichpayPayinForm;
use App\Livewire\Agent\AgentList;
use App\Livewire\Merchant\MerchantList;
use App\Livewire\User\UserList;
use App\Livewire\ApiDocumentation;
use App\Livewire\DepositSummaryReport;
use App\Livewire\WithdrawalSummaryReport;
use App\Livewire\Merchant\MerchantConfigureGateway;
use App\Livewire\PaymentGateway\GatewayAccountList;
use App\Livewire\PaymentGateway\GatewayChannelList;
use App\Livewire\PaymentGateway\ChannelParameter;
use App\Livewire\PaymentGateway\ChannelParameterDetails;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/home', [HomeController::class, 'index']);
Route::get('/table', [HomeController::class, 'datatable']);

Route::get('/', LoginPage::class)->name('login');
Route::get('/login', LoginPage::class)->name('login');
Route::get('/dashboard', DashboardPage::class)->name('dashboard');
Route::get('/profile', ProfilePage::class)->name('user.profile');
Route::get('/transactions/deposit', DepositTransactionList::class)->name('transactions.deposit');
Route::get('/transactions/withdraw', WithdrawTransactionList::class)->name('transactions.withdraw');
Route::get('lang', [LanguageController::class, 'change'])->name("change.lang");
// FC Start
Route::get('/generate/FCQR', GenerateqrForm::class)->name('generate.FCQR');
Route::get('/showQR/{recordID}', ShowQR::class)->name('showQR');
Route::get('/invoice-qrcode-list', QrcodeList::class)->name('invoice.qrcode.list');


Route::controller(AJAXController::class)->group(function () {
   
    Route::get('/get-record/{id}', 'getRecord');
    // Route::post('/gateway/toggle-status', 'toggleStatus')->name('gateway.toggleStatus');
    // Route::get('/get-gatewayAccount-record/{id}', 'getgatewayAccountRecord');
    // Route::post('/saveGatewayAccountData', 'saveGatewayAccountData')->name('GatewayAccount.save');

});

// Route::get('fc/r2pdeposit/{amount}/{invoice_number}/{customer_name}', 'fcs2pDeposit')->name('fc.s2p.Deposit');

Route::get('/fc/r2pdeposit/{amount}/{invoice_number}/{customer_name}', DepositFormRichpay::class)->name('fc.r2pdeposit');
Route::get('/r2pPayin', RichpayPayinForm::class)->name('r2p.deposit.form');

Route::controller(RichPayController::class)->group(function () {
    
    Route::get('/r2pPaymentPage/{frtransaction}', 'paymentPage');
    Route::get('/r2pPaymentPage2/{frtransaction}', 'paymentProcessingPage');
    Route::get('r2p/payinResponse/{frtransaction}', 'payinResponse');
    // Route::get('/r2pPayout', 'r2pPayoutform'); 

    Route::get('/r2p/payintest', 'payintest'); 
    // Route::get('/r2p/payouttest', 'payouttest'); 

    Route::get('sendDepositNotification/{id}', 'sendDepositNotification');
});

// FOR GATEWAY CONFIGURATION START
Route::get('/gateway-account-list', GatewayAccountList::class)->name('gateway.account.list');
Route::get('/payment-channel-list', GatewayChannelList::class)->name('payment.channel.list');
Route::get('/channel-parameter-list', ChannelParameter::class)->name('channel.parameter.list');
Route::get('/parameter-details/{channelId}', ChannelParameterDetails::class)->name('channel.details');
// FOR GATEWAY CONFIGURATION END
Route::get('/agent-lists', AgentList::class)->name('agent.list');
Route::get('/merchant-lists', MerchantList::class)->name('merchant.list');
Route::get('/merchant-configuration-gateway', MerchantConfigureGateway::class)->name('merchant.configure.gateway');
Route::get('/user-lists', UserList::class)->name('user.list');
Route::get('/api/documentation', ApiDocumentation::class)->name('api.documentation');
Route::get('/deposit/summaryReport', DepositSummaryReport::class)->name('deposit.summaryReport');
Route::get('/withdrawal/summaryReport', WithdrawalSummaryReport::class)->name('withdrawal.summaryReport');



Route::controller(IpintPaymentController::class)->group(function () {
    Route::get('/iptCryptoDeposit', 'ipintDepositform');            // Deposit form
    Route::get('ipint/deposit/gatewayResponse', 'ipintDepositGatewayResponse');       // for gateway response
});
Route::get('/ipcrypto/payintest', function () {
    return view('payment-form.ipint.payintest');
});

Route::controller(XprizoPaymentController::class)->group(function () {
    Route::get('/xpzDeposit-USD', 'xpzDepositformUSD');            // Card Deposit form USD
    Route::get('xpz/deposit/gatewayResponse', 'xpzDepositGatewayResponse');       // for gateway response
    //INR UPI payment integration
    Route::get('/xpzUPIpayment', 'xpzUPIdeposit');            // UPI Deposit form 
    Route::get('xpz/UPIdepositResponse/{frtransaction}/{merchantCode}/{channelId}', 'TransactionStatusUPIfun');       // for redirect check transaction status



    Route::get('/xpzDeposit-THB', 'xpzDepositformTHB');            // Card Deposit form USD



    // Route::get('/xpzWithdrawal', 'xpzWithdrawalform');            // Withdrawal form
});
Route::get('/xpz/payintest', function () {
    return view('payment-form.xpz.payintest');
});
Route::get('/xpz/payouttest', function () {
    return view('payment-form.xpz.payouttest');
});


Route::controller(QorePaymentController::class)->group(function () {
    Route::get('/QCardDeposit', 'qoreDepositform');            // Card Deposit form 
    Route::get('qp/deposit/gatewayResponse', 'qpDepositGatewayResponse');       // for gateway response
   
});


Route::get('/pusher', function () {
    return view('pusher');
});
Route::get('/pusher2', function () {
    return view('pusher2');
});
Route::get('/post',[PostController::class,'showForm']);
Route::post('/user/postsave',[PostController::class,'save'])->name('post.save');

 
