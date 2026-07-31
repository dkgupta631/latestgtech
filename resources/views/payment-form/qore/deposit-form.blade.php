
@extends('components.layouts.controllerlayouts')
@section('content')
<?php
    $referenceNo="GZTRN".time().generateRandomString(3);
    function generateRandomString($length = 3) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {$randomString .= $characters[rand(0, $charactersLength - 1)];}
        return $randomString;
    }
?>
<style>
    .auth-form {
        padding: 20px 20px !important;
    }
    .form-control {
        height: 3rem !important;
        border: 2px solid gray;
    }
    .justify-content-center {
        margin-top: 10px;
    }
 /* Fullscreen spinner container */
    .spinner-container {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Opaque background */
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    .spinner-container img {
        width: 100px;
        height: 100px;
    }
</style>
 <div class="account-pages">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-7">
                        <div class="account-card-box">
                            <div class="card mb-0">
                                <div class="card-body">
                                    <div class="text-center">
                                        <h2 class="text-muted text-uppercase py-3"><b>GTech Card Deposit USD</b></h2>
                                    </div>
                                    <form role="form" action="{{ route('apiroute.CardDepositApi') }}" method="GET" id="paymentForm" class="parsley-examples" data-parsley-validate novalidate>
                                    @csrf
                                        <input type="hidden" name="merchant_code" value="testmerchant005">
                                        <input type="hidden" name="channel_id" value="8">   {{-- // for local 7 , for live 8 --}}
                                        <input type="hidden" name="callback_url" value="{{ route('apiroute.DepositResponse') }}">
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-md-4 col-form-label"><strong>Reference ID</strong><span class="text-danger">*</span></label>
                                            <div class="col-md-8">
                                                <input class="form-control" name="referenceId" placeholder="Enter Reference ID" value="<?php echo $referenceNo; ?>" readonly type="text">
                                            </div>
                                        </div> 
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-md-4 col-form-label"><strong>Currency</strong><span class="text-danger">*</span></label>
                                            <div class="col-md-8">
                                                <select class="form-control" name="Currency" required>
                                                    <option value="">---</option>
                                                    <option value="EUR">EUR</option>
                                                    <option value="USD" selected>USD</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-md-4 col-form-label"><strong>Amount</strong><span class="text-danger">*</span></label>
                                            <div class="col-md-8">
                                                <input class="form-control" name="amount" id="amountInput" placeholder="Enter your Amount" value="2" type="text">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="customer_name" class="col-md-4 col-form-label"><strong>Card Holder Name</strong><span class="text-danger">*</span></label>
                                            <div class="col-md-8">
                                                <input  class="form-control" name="customer_name" placeholder="Enter cumtomer name" type="text" value="dk gupta">
                                                @error('customer_name')
                                                <label class="error" for="customer_name">{{ $message }}</label> 
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="card_number" class="col-md-4 col-form-label"><strong>Card Number</strong><span class="text-danger">*</span></label>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control " name="card_number" id="card_number" placeholder="Enter Card number" maxlength='16' value="4111111111111111">
                                            </div>
                                            @error('card_number')
                                                <div class="alert alert-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group row">
                                            <label for="expiration" class="col-md-4 col-form-label"><strong>Expiration</strong><span class="text-danger">*</span></label>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control expirationInput" name="expiration" id="expiration"  maxlength='5' placeholder="MM/YY" value="12/2027">
                                                    <p class="expirationInput-warning text text-danger" style="display:none">Please fillup correct!</p>
                                            </div>
                                            @error('expiration')
                                                <div class="alert alert-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group row">
                                            <label for="cvv" class="col-md-4 col-form-label"><strong>CVC</strong><span class="text-danger">*</span></label>
                                            <div class="col-md-8">
                                                    <input type="text" class="form-control" name="cvv" id="cvv" placeholder="Enter your cvv" maxlength='3' value="123">
                                            </div>
                                            @error('cvv')
                                                <div class="alert alert-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <br/>
                                        <!-- Spinner -->
                                        <div class="spinner-container">
                                            <img src="https://i.gifer.com/ZZ5H.gif" alt="Loading..."> <!-- Replace with your spinner image URL -->
                                        </div>
                                        <div class="form-group text-center">
                                            <button type="submit" id="submitBtn" class="card-btn btn btn-block btn-lg btn-primary waves-effect waves-light">Pay Now <span id="amountLabel">2</span>$ <i class="mdi mdi-arrow-right"></i></button>
                                        </div>
                                    </form>
                                </div> <!-- end card-body -->
                            </div>
                            <!-- end card -->
                        </div>
                    </div> <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->
<script>
    const form = document.getElementById('paymentForm');
    const spinnerContainer = document.querySelector('.spinner-container');
    form.addEventListener('submit', function () {
        var btn = $("#submitBtn");
                btn.html('<span class="spinner-border spinner-border-sm"></span> Processing...'); 
                btn.prop("disabled", true);
        spinnerContainer.style.display = 'flex'; // Show spinner with opaque background
    });
</script>
<script>
$(document).ready(function() {
    $("#amountInput").on("input", function() {
        let amount = $(this).val();
        // Regular expression to allow only integers or decimals
        let regex = /^[0-9]*\.?[0-9]*$/;
        // If input doesn't match, show alert and clear invalid characters
        if (!regex.test(amount)) {
            alert("Amount should be integer or decimal value !");
            // Remove invalid characters
            $(this).val(amount.replace(/[^0-9.]/g, ''));
            amount = $(this).val();
        }
        // If input is empty, set amount = 0
        if (amount === "") {
            amount = 0;
        }
        // Update the button text dynamically
        $("#amountLabel").text(amount);
    });
});
 // On keyUp validate Expiry Moth and Year START
    $(document).ready(function(){
        $('.expirationInput').on('keyup', function(){
            var val = $(this).val();
            // Remove any non-numeric characters
            val = val.replace(/\D/g,'');
            if(val.length > 2){
                // If more than 2 characters, trim it
                val = val.slice(0,2) + '/' + val.slice(2);
            }
            else if (val.length === 2){
                // If exactly 2 characters, add "/"
                val = val + '/';
            }
            $(this).val(val);

            // Check if the entered date is in the future
            var today = new Date();
            var currentYear = today.getFullYear().toString().substr(-2);
            var currentMonth = today.getMonth() + 1;
            var enteredYear = parseInt(val.substr(3));
            var enteredMonth = parseInt(val.substr(0, 2));

            if (enteredYear < currentYear || (enteredYear == currentYear && enteredMonth < currentMonth)) {
                // Entered date is not in the future, clear the input
                $('.expirationInput-warning').css("display", "block");
                $('.expirationInput').addClass("inputerror");
                $('button.card-btn').prop('disabled', true);
                // alert("Please enter a future expiry date.");
            }else{
                $('.expirationInput-warning').css("display", "none");
                $('.expirationInput').removeClass("inputerror");
                $('button.card-btn').prop('disabled', false);
            }
        });
    });
    // On keyUp validate Expiry Moth and Year END

</script>
@endsection
