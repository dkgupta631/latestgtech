<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>{{ $title ?? __('messages.PAYMENT GATEWAY') }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="{{ __('messages.International Payment Gateway System') }}" name="description" />
        <meta content="KHBiz" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- App favicon -->
        <link rel="shortcut icon" href="{{ URL::to('newassets/images/khbiz-icon.png') }}">
        <!-- App css -->
        <link href="{{ URL::to('newassets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap-stylesheet" />
        <link href="{{ URL::to('newassets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ URL::to('newassets/css/app.min.css') }}" rel="stylesheet" type="text/css"  id="app-stylesheet" />
        <!-- Notification css (Toastr) -->
        <link href="{{ URL::to('newassets/libs/toastr/toastr.min.css') }}" rel="stylesheet" type="text/css" />
         <!-- Toster CSS START Livewire-->
        <link rel="stylesheet" href="https://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
        <link href="{{ URL::to('newassets/css/mm-theme.css') }}?v={{ filemtime(public_path('newassets/css/mm-theme.css')) }}" rel="stylesheet" type="text/css" />
        <!-- Toster CSS END Livewire-->
        @livewireStyles
    </head>

    <body class="authentication-bg">

        <div class="account-pages pt-5 my-5">
            <div class="container">

                {{ $slot }}
    
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->

        <!-- Vendor js -->
        <script src="{{ URL::to('newassets/js/vendor.min.js') }}"></script>
        <!-- Toastr js -->
        <script src="{{ URL::to('newassets/libs/toastr/toastr.min.js') }}"></script>
        <script src="{{ URL::to('newassets/js/pages/toastr.init.js') }}"></script>
        <!-- App js -->
        <script src="{{ URL::to('newassets/js/app.min.js') }}"></script>

       <!-- Toster JS START For Livewire-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script>
            document.addEventListener("livewire:init", () => {
                Livewire.on("toast", (event) => {
                    toastr[event.notify](event.message);
                });
            });
        </script>
        <!-- Toster JS END For Livewire-->
            {{-- toastr js START for LARAVEL controller--}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
        <script>
            $(document).ready(function() {
                toastr.options.timeOut = 10000;
                @if (Session::has('error'))
                    toastr.error('{{ Session::get('error') }}');
                @elseif(Session::has('success'))
                    toastr.success('{{ Session::get('success') }}');
                @elseif(Session::has('warning'))
                    toastr.warning('{{ Session::get('warning') }}');
                @endif
            });
        </script>
        {{-- toastr js END for LARAVEL controller--}}
        @livewireScripts
    </body>
</html>

