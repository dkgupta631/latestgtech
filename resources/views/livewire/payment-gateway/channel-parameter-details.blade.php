<div>
    <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="/">GTech</a></li>
                            <li class="breadcrumb-item active">{{ __('messages.Parameter Details') }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ __('messages.Parameter Details') }}</h4>
                </div>
            </div>
        </div>     
    <!-- end page title --> 
    <div class="card">
        <div class="card-body">
            <div class="mm-detail-head">
                <span class="mm-detail-chip"><i class="mdi mdi-bank-outline"></i> {{ __('messages.Gateway') }}: <b>{{ $gatewayDetails->gatewayAccount->gateway_name }}</b></span>
                <span class="mm-detail-chip"><i class="mdi mdi-swap-horizontal"></i> {{ __('messages.Payment Channel') }}: <b>{{ ucfirst($gatewayDetails->channel_name) }}</b></span>
            </div>
            <form wire:submit.prevent="save">
                <div class="table-responsive mm-table-scroll">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('messages.Parameter Name') }}</th>
                                <th>{{ __('messages.Parameter Value') }}</th>
                                <th>{{ __('messages.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $index => $row)
                            <tr>
                                <td>
                                    <input type="text" wire:model="rows.{{ $index }}.parameter_name" class="form-control" placeholder="{{ __('messages.Name') }}">
                                </td>
                                <td>
                                    <input type="text" wire:model="rows.{{ $index }}.parameter_value" class="form-control" placeholder="{{ __('messages.Value') }}">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-danger" wire:click="removeRow({{ $index }})"><i class="mdi mdi-trash-can-outline"></i> {{ __('messages.Remove') }}</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mm-form-actions">
                    <button type="button" class="btn btn-outline-primary" wire:click="addRow"><i class="mdi mdi-plus"></i> {{ __('messages.Add Row') }}</button>
                    <div class="mm-actions-right">
                        <a href="{{ route('channel.parameter.list') }}" wire:navigate class="btn btn-secondary"><i class="mdi mdi-arrow-left"></i> {{ __('messages.Back') }}</a>
                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled" wire:target="save"><i class="mdi mdi-content-save-outline"></i> {{ __('messages.Save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success mt-2">{{ session('message') }}</div>
    @endif
</div>
