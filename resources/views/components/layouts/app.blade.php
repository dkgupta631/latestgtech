<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>{{ $title ?? __('messages.PAYMENT GATEWAY') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="{{ __('messages.International Payment Gateway System') }}" name="description" />
    <meta content="Coderthemes" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::to('newassets/images/favicon.ico') }}">
    <!-- App css -->
    <link href="{{ URL::to('newassets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap-stylesheet" />
    <link href="{{ URL::to('newassets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::to('newassets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-stylesheet" />
    <link href="{{ URL::to('newassets/css/customStyle.css') }}" rel="stylesheet" type="text/css" id="app-stylesheet" />
    <!-- Table datatable css -->
    <link href="{{ URL::to('newassets/libs/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::to('newassets/libs/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::to('newassets/libs/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::to('newassets/libs/datatables/select.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

    {{-- <!-- Plugins css -->
    <link href="{{ URL::to('newassets/libs/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet" />
    <link href="{{ URL::to('newassets/libs/switchery/switchery.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::to('newassets/libs/multiselect/multi-select.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::to('newassets/libs/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Sweet Alert-->
        <link href="{{ URL::to('newassets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" /> --}}

    <!-- Notification css (Toastr) -->
    <link href="{{ URL::to('newassets/libs/toastr/toastr.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Toster CSS START for Livewire and Laravel both-->
    <link rel="stylesheet" href="https://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
    <!-- Toster CSS END for Livewire and Laravel both-->
    <!-- Custom box css -->
    <link href="{{ URL::to('newassets/libs/custombox/custombox.min.css') }}" rel="stylesheet">
        <link href="{{ URL::to('newassets/css/mm-theme.css') }}" rel="stylesheet" type="text/css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireStyles
    {{-- optional bundle for instant Pusher alerts; the site must work without a build --}}
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/js/app.js'])
    @endif
</head>

<body>

    <!-- Begin page -->
    <div id="wrapper">

            <!-- Topbar Start -->
           <livewire:layouts.header />
            <!-- end Topbar -->
            <!-- ========== Left Sidebar Start ========== -->
            <livewire:layouts.sidebar />
            <!-- Left Sidebar End -->

            <div class="content-page">
                <div class="content"><!-- Start Content-->
                    <div class="container-fluid">
            
                         {{ $slot }}

                    </div> <!-- end container-fluid -->
                </div> <!-- end content -->

                <!-- Footer Start -->
                <footer class="footer">
                    <div class="mm-footer">
                        <span>2025 - {{ date('Y') }} &copy; {{ __('messages.powered by') }} <a href="https://zaffrantech.com/" target="_blank" rel="noopener">ZaffranTech</a></span>
                        <span class="mm-footer-secure"><i class="mdi mdi-shield-check"></i> {{ __('messages.Secure payment infrastructure') }}</span>
                    </div>
                </footer>
                <!-- end Footer -->

            </div>


    </div>
    <!-- END wrapper -->


    @livewireScripts
    @php
        $mmI18n = [];
        foreach (['Sound alerts on - click to mute', 'Sound alerts off - click to turn on', 'Click anywhere to enable sound alerts', 'Click anywhere on the page to enable sound alerts.', 'Sound is blocked by the browser', 'New transaction'] as $mmKey) {
            $mmI18n[$mmKey] = __('messages.' . $mmKey);
        }
    @endphp
    <script>
        window.mmI18n = {!! json_encode($mmI18n, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!};
    </script>
    <script src="{{ URL::to('newassets/js/mm-alerts.js') }}"></script>

    <script>
        // mobile: tap outside the sidebar closes it
        document.addEventListener('click', function (e) {
            if (window.innerWidth < 992 && document.body.classList.contains('sidebar-enable')
                && !e.target.closest('.left-side-menu, .button-menu-mobile')) {
                document.body.classList.remove('sidebar-enable');
            }
        });
    </script>

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
    <script>
    Livewire.hook('message.processed', (message, component) => {
        const chartData = component.serverMemo.data.chartData;
        window.dispatchEvent(new CustomEvent('chart-updated', { detail: chartData }));
    });
    </script>
    <!-- Vendor js -->
    <script src="{{ URL::to('newassets/js/vendor.min.js') }}"></script>
    <!--Morris Chart-->
    {{-- <script src="{{ URL::to('newassets/libs/morris-js/morris.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/raphael/raphael.min.js') }}"></script>
    <!-- Dashboard init js-->
    <script src="{{ URL::to('newassets/js/pages/dashboard.init.js') }}"></script> --}}
    <!-- Chart JS -->
        {{-- <script src="{{ URL::to('newassets/libs/chart-js/Chart.bundle.min.js') }}"></script> --}}
    <!-- Init js -->
        {{-- <script src="{{ URL::to('newassets/js/pages/chartjs.init.js') }}"></script> --}}
    <!-- App js -->
    <script src="{{ URL::to('newassets/js/app.min.js') }}"></script>

    <!-- Datatable plugin js -->
    <script src="{{ URL::to('newassets/libs/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/datatables/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/datatables/dataTables.buttons.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/datatables/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/datatables/buttons.html5.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/datatables/buttons.print.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/datatables/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/datatables/dataTables.select.min.js') }}"></script>
    {{-- <!-- Plugins Js -->
    <script src="{{ URL::to('newassets/libs/bootstrap-tagsinput/bootstrap-tagsinput.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/switchery/switchery.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/multiselect/jquery.multi-select.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/jquery-quicksearch/jquery.quicksearch.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/select2/select2.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/jquery-mockjax/jquery.mockjax.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/autocomplete/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ URL::to('newassets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
         <!-- form advanced init -->
    <script src="{{ URL::to('newassets/js/pages/form-advanced.init.js') }}"></script>
        <!-- Sweet Alerts js -->
        <script src="{{ URL::to('newassets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
        <!-- Sweet alert init js-->
        <script src="{{ URL::to('newassets/js/pages/sweet-alerts.init.js') }}"></script> --}}

    <!-- Datatables init -->
    @if (app()->getLocale() === 'th')
    <script>
        $.extend(true, $.fn.dataTable.defaults, { language: {
            search: 'ค้นหา:', lengthMenu: 'แสดง _MENU_ รายการ', info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
            infoEmpty: 'แสดง 0 ถึง 0 จาก 0 รายการ', infoFiltered: '(กรองจาก _MAX_ รายการ)', zeroRecords: 'ไม่พบข้อมูล',
            emptyTable: 'ไม่มีข้อมูล', paginate: { first: 'หน้าแรก', last: 'หน้าสุดท้าย', next: 'ถัดไป', previous: 'ก่อนหน้า' },
            buttons: { copy: 'คัดลอก', excel: 'Excel', pdf: 'PDF', print: 'พิมพ์', copyTitle: 'คัดลอกแล้ว', copySuccess: { _: 'คัดลอก %d แถว', 1: 'คัดลอก 1 แถว' } }
        } });
    </script>
    @endif
    <script src="{{ URL::to('newassets/js/pages/datatables.init.js') }}"></script>

    <!-- Toastr js -->
        <script src="{{ URL::to('newassets/libs/toastr/toastr.min.js') }}"></script>
        <script src="{{ URL::to('newassets/js/pages/toastr.init.js') }}"></script>
    
    <!-- Modal-Effect -->
        <script src="{{ URL::to('newassets/libs/custombox/custombox.min.js') }}"></script>
    
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

    
</body>

</html>