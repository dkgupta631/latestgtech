<div>
      <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="/">GTech</a></li>
                                        <li class="breadcrumb-item active">{{ __('messages.Dashboard') }}</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">{{ __('messages.Dashboard') }}</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
    <div class="content-body">
        <div class="container-fluid" >
            <div class="row invoice-card-row">
                <div class="col-xl-3 col-xxl-3 col-sm-6">
                    <div class="card bg-warning invoice-card">
                        <div class="card-body d-flex py-2 align-items-center">
                            <div class="icon me-3"  >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" width="33px" height="32px">
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M12 7.5a2.25 2.25 0 100 4.5 2.25 2.25 0 000-4.5z" />
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 011.5 14.625v-9.75zM8.25 9.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM18.75 9a.75.75 0 00-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 00.75-.75V9.75a.75.75 0 00-.75-.75h-.008zM4.5 9.75A.75.75 0 015.25 9h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H5.25a.75.75 0 01-.75-.75V9.75z" clip-rule="evenodd" />
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M2.25 18a.75.75 0 000 1.5c5.4 0 10.63.722 15.6 2.075 1.19.324 2.4-.558 2.4-1.82V18.75a.75.75 0 00-.75-.75H2.25z" />
                                </svg>
                            </div>
                            <div >
                                <h2 class="text-white invoice-num fw-bold">{{ $totalDepositCount ?? '00'}} </h2>
                                <span class="text-white fs-16">{{__('messages.Total Transaction Count')}} </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-xxl-3 col-sm-6">
                    <div class="card bg-success invoice-card">
                        <div class="card-body d-flex py-2 align-items-center">
                            <div class="icon me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" width="35px" height="34px">
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M10.464 8.746c.227-.18.497-.311.786-.394v2.795a2.252 2.252 0 01-.786-.393c-.394-.313-.546-.681-.546-1.004 0-.323.152-.691.546-1.004zM12.75 15.662v-2.824c.347.085.664.228.921.421.427.32.579.686.579.991 0 .305-.152.671-.579.991a2.534 2.534 0 01-.921.42z" />
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 6a.75.75 0 00-1.5 0v.816a3.836 3.836 0 00-1.72.756c-.712.566-1.112 1.35-1.112 2.178 0 .829.4 1.612 1.113 2.178.502.4 1.102.647 1.719.756v2.978a2.536 2.536 0 01-.921-.421l-.879-.66a.75.75 0 00-.9 1.2l.879.66c.533.4 1.169.645 1.821.75V18a.75.75 0 001.5 0v-.81a4.124 4.124 0 001.821-.749c.745-.559 1.179-1.344 1.179-2.191 0-.847-.434-1.632-1.179-2.191a4.122 4.122 0 00-1.821-.75V8.354c.29.082.559.213.786.393l.415.33a.75.75 0 00.933-1.175l-.415-.33a3.836 3.836 0 00-1.719-.755V6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div >
                                <h2 class="text-white invoice-num fw-bold"><?= number_format((int) $total_transactions_amount_today, 2) ?></h2>
                                <span class="text-white fs-16">{{__("messages.Today's ")}} {{__('messages.Transaction Amount')}} </span>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-xxl-3 col-sm-6">
                    <div class="card bg-info invoice-card">
                        <div class="card-body d-flex py-2 align-items-center">
                            <div class="icon me-3">
                                <svg width="33px" height="32px">
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M31.963,30.931 C31.818,31.160 31.609,31.342 31.363,31.455 C31.175,31.538 30.972,31.582 30.767,31.583 C30.429,31.583 30.102,31.463 29.845,31.243 L25.802,27.786 L21.758,31.243 C21.502,31.463 21.175,31.583 20.837,31.583 C20.498,31.583 20.172,31.463 19.915,31.243 L15.872,27.786 L11.829,31.243 C11.622,31.420 11.370,31.534 11.101,31.572 C10.832,31.609 10.558,31.569 10.311,31.455 C10.065,31.342 9.857,31.160 9.710,30.931 C9.565,30.703 9.488,30.437 9.488,30.167 L9.488,17.416 L2.395,17.416 C2.019,17.416 1.658,17.267 1.392,17.001 C1.126,16.736 0.976,16.375 0.976,16.000 L0.976,6.083 C0.976,4.580 1.574,3.139 2.639,2.076 C3.703,1.014 5.146,0.417 6.651,0.417 L26.511,0.417 C28.016,0.417 29.459,1.014 30.524,2.076 C31.588,3.139 32.186,4.580 32.186,6.083 L32.186,30.167 C32.186,30.437 32.109,30.703 31.963,30.931 ZM9.488,6.083 C9.488,5.332 9.189,4.611 8.657,4.080 C8.125,3.548 7.403,3.250 6.651,3.250 C5.898,3.250 5.177,3.548 4.645,4.080 C4.113,4.611 3.814,5.332 3.814,6.083 L3.814,14.583 L9.488,14.583 L9.488,6.083 ZM29.348,6.083 C29.348,5.332 29.050,4.611 28.517,4.080 C27.985,3.548 27.263,3.250 26.511,3.250 L11.559,3.250 C12.059,4.111 12.324,5.088 12.325,6.083 L12.325,27.092 L14.950,24.840 C15.207,24.620 15.534,24.500 15.872,24.500 C16.210,24.500 16.537,24.620 16.794,24.840 L20.837,28.296 L24.880,24.840 C25.137,24.620 25.463,24.500 25.802,24.500 C26.140,24.500 26.467,24.620 26.724,24.840 L29.348,27.092 L29.348,6.083 ZM25.092,20.250 L16.581,20.250 C16.205,20.250 15.844,20.101 15.578,19.835 C15.312,19.569 15.162,19.209 15.162,18.833 C15.162,18.457 15.312,18.097 15.578,17.831 C15.844,17.566 16.205,17.416 16.581,17.416 L25.092,17.416 C25.469,17.416 25.829,17.566 26.096,17.831 C26.362,18.097 26.511,18.457 26.511,18.833 C26.511,19.209 26.362,19.569 26.096,19.835 C25.829,20.101 25.469,20.250 25.092,20.250 ZM25.092,14.583 L16.581,14.583 C16.205,14.583 15.844,14.434 15.578,14.168 C15.312,13.903 15.162,13.542 15.162,13.167 C15.162,12.791 15.312,12.430 15.578,12.165 C15.844,11.899 16.205,11.750 16.581,11.750 L25.092,11.750 C25.469,11.750 25.829,11.899 26.096,12.165 C26.362,12.430 26.511,12.791 26.511,13.167 C26.511,13.542 26.362,13.903 26.096,14.168 C25.829,14.434 25.469,14.583 25.092,14.583 ZM25.092,8.916 L16.581,8.916 C16.205,8.916 15.844,8.767 15.578,8.501 C15.312,8.236 15.162,7.875 15.162,7.500 C15.162,7.124 15.312,6.764 15.578,6.498 C15.844,6.232 16.205,6.083 16.581,6.083 L25.092,6.083 C25.469,6.083 25.829,6.232 26.096,6.498 C26.362,6.764 26.511,7.124 26.511,7.500 C26.511,7.875 26.362,8.236 26.096,8.501 C25.829,8.767 25.469,8.916 25.092,8.916 Z"></path>
                                </svg>
                            </div>
                            <div >
                                <h2 class="text-white invoice-num fw-bold">{{ $total_payout_count ?? '00' }}</h2>
                                <span class="text-white fs-16">{{__('messages.Total Payout Count')}} </span>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-xxl-3 col-sm-6">
                    <div class="card bg-secondary invoice-card">
                        <div class="card-body d-flex py-2 align-items-center">
                            <div class="icon me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" width="35px" height="34px">
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M12.75 12.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM7.5 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM8.25 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM9.75 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM10.5 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM12.75 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM14.25 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM15 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM16.5 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM15 12.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM16.5 13.5a.75.75 0 100-1.5.75.75 0 000 1.5z" />
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M6.75 2.25A.75.75 0 017.5 3v1.5h9V3A.75.75 0 0118 3v1.5h.75a3 3 0 013 3v11.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V7.5a3 3 0 013-3H6V3a.75.75 0 01.75-.75zm13.5 9a1.5 1.5 0 00-1.5-1.5H5.25a1.5 1.5 0 00-1.5 1.5v7.5a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5v-7.5z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-white invoice-num fw-bold"><?= number_format((int) $total_transactions_amount_month, 2) ?></h2>
                                <span class="text-white fs-16">{{__('messages.Monthly')}} {{__('messages.Transaction Amount')}} </span>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            {{-- {{$data = Session::get('auth')->role_name }}
            {{ dd($data); }} --}}
            @if (Session::get('auth')->role_name != 'Admin')
            <div class="row invoice-card-row">
                <div class="col-xl-3 col-xxl-3 col-sm-6">
                    <div class="card bg-warning invoice-card">
                        <div class="card-body d-flex py-2 align-items-center">
                            <div class="icon me-3"  >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" width="35px" height="34px">
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M10.464 8.746c.227-.18.497-.311.786-.394v2.795a2.252 2.252 0 01-.786-.393c-.394-.313-.546-.681-.546-1.004 0-.323.152-.691.546-1.004zM12.75 15.662v-2.824c.347.085.664.228.921.421.427.32.579.686.579.991 0 .305-.152.671-.579.991a2.534 2.534 0 01-.921.42z" />
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 6a.75.75 0 00-1.5 0v.816a3.836 3.836 0 00-1.72.756c-.712.566-1.112 1.35-1.112 2.178 0 .829.4 1.612 1.113 2.178.502.4 1.102.647 1.719.756v2.978a2.536 2.536 0 01-.921-.421l-.879-.66a.75.75 0 00-.9 1.2l.879.66c.533.4 1.169.645 1.821.75V18a.75.75 0 001.5 0v-.81a4.124 4.124 0 001.821-.749c.745-.559 1.179-1.344 1.179-2.191 0-.847-.434-1.632-1.179-2.191a4.122 4.122 0 00-1.821-.75V8.354c.29.082.559.213.786.393l.415.33a.75.75 0 00.933-1.175l-.415-.33a3.836 3.836 0 00-1.719-.755V6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div >
                                <h2 class="text-white invoice-num fw-bold">
                                {{ $totalDepositSum ?? '' }}
                                </h2>
                                <span class="text-white fs-16"> {{__('messages.Total Deposit')}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-xxl-3 col-sm-6">
                    <div class="card bg-success invoice-card">
                        <div class="card-body d-flex py-2 align-items-center">
                            <div class="icon me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" width="35px" height="34px">
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M10.464 8.746c.227-.18.497-.311.786-.394v2.795a2.252 2.252 0 01-.786-.393c-.394-.313-.546-.681-.546-1.004 0-.323.152-.691.546-1.004zM12.75 15.662v-2.824c.347.085.664.228.921.421.427.32.579.686.579.991 0 .305-.152.671-.579.991a2.534 2.534 0 01-.921.42z" />
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 6a.75.75 0 00-1.5 0v.816a3.836 3.836 0 00-1.72.756c-.712.566-1.112 1.35-1.112 2.178 0 .829.4 1.612 1.113 2.178.502.4 1.102.647 1.719.756v2.978a2.536 2.536 0 01-.921-.421l-.879-.66a.75.75 0 00-.9 1.2l.879.66c.533.4 1.169.645 1.821.75V18a.75.75 0 001.5 0v-.81a4.124 4.124 0 001.821-.749c.745-.559 1.179-1.344 1.179-2.191 0-.847-.434-1.632-1.179-2.191a4.122 4.122 0 00-1.821-.75V8.354c.29.082.559.213.786.393l.415.33a.75.75 0 00.933-1.175l-.415-.33a3.836 3.836 0 00-1.719-.755V6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div >
                                <h2 class="text-white invoice-num fw-bold">{{ $total_payout ?? '00' }}</h2>
                                <span class="text-white fs-16">{{__('messages.Total Payout')}} </span>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-xxl-3 col-sm-6">
                    <div class="card bg-info invoice-card">
                        <div class="card-body d-flex py-2 align-items-center">
                            <div class="icon me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" width="35px" height="34px">
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M10.464 8.746c.227-.18.497-.311.786-.394v2.795a2.252 2.252 0 01-.786-.393c-.394-.313-.546-.681-.546-1.004 0-.323.152-.691.546-1.004zM12.75 15.662v-2.824c.347.085.664.228.921.421.427.32.579.686.579.991 0 .305-.152.671-.579.991a2.534 2.534 0 01-.921.42z" />
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 6a.75.75 0 00-1.5 0v.816a3.836 3.836 0 00-1.72.756c-.712.566-1.112 1.35-1.112 2.178 0 .829.4 1.612 1.113 2.178.502.4 1.102.647 1.719.756v2.978a2.536 2.536 0 01-.921-.421l-.879-.66a.75.75 0 00-.9 1.2l.879.66c.533.4 1.169.645 1.821.75V18a.75.75 0 001.5 0v-.81a4.124 4.124 0 001.821-.749c.745-.559 1.179-1.344 1.179-2.191 0-.847-.434-1.632-1.179-2.191a4.122 4.122 0 00-1.821-.75V8.354c.29.082.559.213.786.393l.415.33a.75.75 0 00.933-1.175l-.415-.33a3.836 3.836 0 00-1.719-.755V6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-white invoice-num fw-bold">{{ $totalFee ?? '00' }}</h2>
                                <span class="text-white fs-16">{{__('messages.Total Fee')}} </span>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-xl-3 col-xxl-3 col-sm-6">
                    <div class="card bg-secondary invoice-card">
                        <div class="card-body d-flex py-2 align-items-center">
                            <div class="icon me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" width="35px" height="34px">
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M12.75 12.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM7.5 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM8.25 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM9.75 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM10.5 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM12.75 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM14.25 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM15 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM16.5 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM15 12.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM16.5 13.5a.75.75 0 100-1.5.75.75 0 000 1.5z" />
                                    <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M6.75 2.25A.75.75 0 017.5 3v1.5h9V3A.75.75 0 0118 3v1.5h.75a3 3 0 013 3v11.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V7.5a3 3 0 013-3H6V3a.75.75 0 01.75-.75zm13.5 9a1.5 1.5 0 00-1.5-1.5H5.25a1.5 1.5 0 00-1.5 1.5v7.5a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5v-7.5z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                            <h2 class="text-white invoice-num fw-bold">
                                <?=  number_format((int) $totalDepositSum-($totalFee+$total_payout), 2); ?>
                                </h2>
                                <span class="text-white fs-16">{{__('messages.Total Available Balance For Payout')}} </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                
            </div>
            @endif
            
            {{-- <div class="row">
                <div class="col-12">
                    <div class="card-box">

                        <div class="row">
                            <div class="col-lg-6">
                                <div>
                                    <h4 class="header-title">Line Chart</h4>
                                    <p class="sub-header">
                                        A line chart is a way of plotting data points on a line. Often, it is
                                        used to show trend data, and the comparison of two data sets.
                                    </p>

                                    <div class="p-3">
                                        <canvas id="lineChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mt-5 mt-lg-0">
                                    <h4 class="header-title">Bar Chart</h4>
                                    <p class="sub-header">
                                        A bar chart is a way of showing data as bars. It is sometimes used to
                                        show trend data, and the comparison of multiple data sets side by side.
                                    </p>

                                    <div class="p-3">
                                        <canvas id="bar" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->

                        <div class="row mt-3">
                            <div class="col-lg-6">
                                <div class="mt-5">
                                    <h4 class="header-title">Pie Chart</h4>
                                    <p class="sub-header">
                                        Pie and doughnut charts are probably the most commonly used chart there
                                        are. They are divided into segments, the arc of each segment shows the
                                        proportional value of each piece of data.
                                    </p>

                                    <div class="p-3">
                                        <canvas id="pie" height="260"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mt-5">
                                    <h4 class="header-title">Donut Chart</h4>
                                    <p class="sub-header">
                                        Pie and doughnut charts are probably the most commonly used chart there
                                        are. They are divided into segments, the arc of each segment shows the
                                        proportional value of each piece of data.
                                    </p>

                                    <div class="p-3">
                                        <canvas id="doughnut" height="260"></canvas>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- end row -->


                        

                    </div>
                </div><!-- end col-->

            </div> --}}
            <!-- end row -->
            
             <livewire:chart-section />
            @if (Session::get('auth')->role_name == 'Admin')
                <div class="row invoice-card-row">
                    <div class="col-xl-3 col-xxl-3 col-sm-6">
                        <div class="card bg-warning invoice-card">
                            <div class="card-body d-flex py-2 align-items-center">
                                <div class="icon me-3"  >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" width="33px" height="32px">
                                        <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M10.5 18.75a.75.75 0 000 1.5h3a.75.75 0 000-1.5h-3z" />
                                        <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M8.625.75A3.375 3.375 0 005.25 4.125v15.75a3.375 3.375 0 003.375 3.375h6.75a3.375 3.375 0 003.375-3.375V4.125A3.375 3.375 0 0015.375.75h-6.75zM7.5 4.125C7.5 3.504 8.004 3 8.625 3H9.75v.375c0 .621.504 1.125 1.125 1.125h2.25c.621 0 1.125-.504 1.125-1.125V3h1.125c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-6.75A1.125 1.125 0 017.5 19.875V4.125z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div >
                                    <h2 class="text-white invoice-num fw-bold">{{ $total_agent ?? '00' }}</h2>
                                    <span class="text-white fs-16">{{__('messages.Total Agent')}} </span>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-xxl-3 col-sm-6">
                        <div class="card bg-success invoice-card">
                            <div class="card-body d-flex py-2 align-items-center">
                                <div class="icon me-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" width="35px" height="34px">
                                        <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z" />
                                    </svg>
                                </div>
                                <div >
                                    <h2 class="text-white invoice-num fw-bold">{{ $total_merchant ?? '00' }}</h2>
                                    <span class="text-white fs-16">{{__('messages.Total Merchant')}} </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-xxl-3 col-sm-6">
                        <div class="card bg-info invoice-card">
                            <div class="card-body d-flex py-2 align-items-center">
                                <div class="icon me-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" width="35px" height="34px">
                                        <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M2.25 2.25a.75.75 0 000 1.5H3v10.5a3 3 0 003 3h1.21l-1.172 3.513a.75.75 0 001.424.474l.329-.987h8.418l.33.987a.75.75 0 001.422-.474l-1.17-3.513H18a3 3 0 003-3V3.75h.75a.75.75 0 000-1.5H2.25zm6.54 15h6.42l.5 1.5H8.29l.5-1.5zm8.085-8.995a.75.75 0 10-.75-1.299 12.81 12.81 0 00-3.558 3.05L11.03 8.47a.75.75 0 00-1.06 0l-3 3a.75.75 0 101.06 1.06l2.47-2.47 1.617 1.618a.75.75 0 001.146-.102 11.312 11.312 0 013.612-3.321z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div >
                                    <h2 class="text-white invoice-num fw-bold">{{ $total_transactions_count ?? '00' }}</h2>
                                    <span class="text-white fs-16">{{__('messages.Total Transaction Count')}} </span>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-xxl-3 col-sm-6">
                        <div class="card bg-secondary invoice-card">
                            <div class="card-body d-flex py-2 align-items-center">
                                <div class="icon me-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" width="35px" height="34px">
                                        <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M4.5 3.75a3 3 0 00-3 3v.75h21v-.75a3 3 0 00-3-3h-15z" />
                                        <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M22.5 9.75h-21v7.5a3 3 0 003 3h15a3 3 0 003-3v-7.5zm-18 3.75a.75.75 0 01.75-.75h6a.75.75 0 010 1.5h-6a.75.75 0 01-.75-.75zm.75 2.25a.75.75 0 000 1.5h3a.75.75 0 000-1.5h-3z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div >
                                    <h2 class="text-white invoice-num fw-bold"> {{ $total_deposit_amount ?? '00' }}</h2>
                                    <span class="text-white fs-16">{{__('messages.Total Transaction Amount')}} </span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- <div id="merchant-agent" style="font-size:12px;">
                    <div class="table-responsive">
                        <table class="table dashboard" style="color:#fff;">
                            <tbody>
                                <tr>
                                    <td style="width: 25%; background-color:#808080; border: 1px solid lightgray" class="header-title">{{ trans('messages.User Name') }}</td>
                                    <td style="width: 25%; color:#000; border: 1px solid lightgray" class="header-title">{{ Session::get('auth')->user_name ?? '' }}</td>
                                    <td style="width: 25%; background-color:#808080; border: 1px solid lightgray" class="header-title">{{ trans('messages.Created Date/Time') }}</td>
                                    <td style="width: 25%; color:#000; border: 1px solid lightgray" class="header-title">{{ Session::get('auth')->created_at ? strtoupper(Session::get('auth')->created_at->format('dMY')) : '' }}</td>
                                </tr>
                                @if (Session::get('auth')->role_name == 'Merchant')
                                <tr>
                                    <td style="width: 25%; background-color:#808080; border: 1px solid lightgray" class="header-title">{{__('messages.Merchant Code')}}</td>
                                    <td style="width: 25%; color:#000; border: 1px solid lightgray" class="header-title">{{ $merchantData->merchant_code ?? '' }}</td>
                                    <td style="width: 25%; background-color:#808080; border: 1px solid lightgray" class="header-title">{{__('messages.Merchant Name')}}</td>
                                    <td style="width: 25%; color:#000; border: 1px solid lightgray" class="header-title">{{ $merchantData->merchant_name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 25%; background-color:#808080; border: 1px solid lightgray" class="header-title">{{__('messages.Agent Code')}}</td>
                                    <td style="width: 25%; color:#000; border: 1px solid lightgray" class="header-title">{{ $agentData->agent_code ?? '' }}</td>
                                    <td style="width: 25%; background-color:#808080; border: 1px solid lightgray" class="header-title">{{__('messages.Agent Name')}}</td>
                                    <td style="width: 25%; color:#000; border: 1px solid lightgray" class="header-title">{{ $agentData->agent_name ?? '' }}</td>
                                </tr>
                                @else
                                <tr>
                                    <td style="width: 25%; background-color:#808080; border: 1px solid lightgray" class="header-title">{{__('messages.Agent Code')}}</td>
                                    <td style="width: 25%; color:#000; border: 1px solid lightgray" class="header-title">{{ $agentData->agent_code ?? '' }}</td>
                                    <td style="width: 25%; background-color:#808080; border: 1px solid lightgray" class="header-title">{{__('messages.Agent Name')}}</td>
                                    <td style="width: 25%; color:#000; border: 1px solid lightgray" class="header-title">{{ $agentData->agent_name ?? '' }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="width: 25%; background-color:#808080; border: 1px solid lightgray" class="header-title">{{__('messages.Email')}}</td>
                                    <td style="width: 25%; color:#000; border: 1px solid lightgray" class="header-title">{{ Session::get('auth')->email ?? '' }}</td>
                                    <td style="width: 25%; background-color:#808080; border: 1px solid lightgray" class="header-title">{{__('messages.Mobile Number')}}</td>
                                    <td style="width: 25%; color:#000; border: 1px solid lightgray" class="header-title">{{ Session::get('auth')->mobile_number ?? '' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div> --}}
            @endif
        </div>
    </div>
<!--Morris Chart-->
    {{-- <script src="{{ URL::to('newassets/libs/morris-js/morris.min.js') }}"></script>
    <!-- Dashboard init js-->
    <script src="{{ URL::to('newassets/js/pages/dashboard.init.js') }}"></script>
     <!-- Init js -->
    <script src="{{ URL::to('newassets/js/pages/chartjs.init.js') }}"></script>
</div> --}}
