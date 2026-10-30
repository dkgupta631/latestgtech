<div>
    <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="/">GTech</a></li>
                            <li class="breadcrumb-item active">{{ __('messages.Channel Parameters') }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ __('messages.Channel Parameters') }}</h4>
                </div>
            </div>
        </div>     
    <!-- end page title --> 
<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-2">
                <label>{{ __('messages.Show') }}</label>
                <select class="form-select form-control"  wire:model.live="perPage">
                    <option value="1">1</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>{{ __('messages.Search') }}</label>
                <input type="text" class="form-control" placeholder="{{ __('messages.Search here') }}..." wire:model.live.debounce.300ms="search">
            </div>
            <div class="col-md-5"></div>
            <div class="col-md-2">
                {{-- <button type="button" class="btn btn-success" wire:click="openCreateModal">
                    <i class="fas fa-plus"></i> Add Parameters
                </button> --}}
            </div>
        </div>

        {{-- Responsive table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('messages.Order Id') }}</th>
                        <th>{{ __('messages.Gateway') }}</th>
                        <th>{{ __('messages.Payment Channel') }}</th>
                        <th>{{ __('messages.Channel Parameters') }}</th>
                        <th>{{ __('messages.Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $idx => $channel)
                        <tr wire:key="row-{{ $channel->id }}">
                            <td>{{ $records->firstItem() + $idx }}</td>
                            <td>{{ $channel->gateway?->gateway_name }}</td>
                            <td>{{ $channel->channel_name }}</td>
                            <td>
                                @php
                                    // You asked to use this mapping:
                                    $parameters = $channel->parameters->pluck('parameter_value', 'parameter_name');
                                @endphp

                                @forelse($parameters as $name => $value)
                                    <span class="badge bg-primary text-wrap me-1 mb-1">
                                        {{ $name }}: {{ $value }}
                                    </span>
                                @empty
                                    <span class="text-muted">{{ __('messages.No parameters') }}</span>
                                @endforelse
                            </td>
                            <td>
                                <a href="parameter-details/{{ base64_encode($channel->id) }}" wire:navigate data-toggle="tooltip" data-placement="right" title="{{ __('messages.Parameter Details') }}">
                                    <button class="btn btn-primary"><i class="fas fa-pencil-alt"></i></button>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-2">
            <div class="text-muted small">
                {{ __('messages.Showing :from–:to of :total', ['from' => $records->firstItem(), 'to' => $records->lastItem(), 'total' => $records->total()]) }}
            </div>
            <div>
                {{ $records->links() }}
            </div>
        </div>
    </div>

    {{-- Livewire-only Modal (Bootstrap-styled, no JS dependency) --}}
    @if($showModal)
        <div class="modal-backdrop fade show"></div>
        <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.3);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEditMode ? 'Edit Parameters' : 'Add Parameters' }}</h5>
                        <button type="button" class="btn-close" aria-label="Close" wire:click="closeModal"></button>
                    </div>

                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">Gateway <span class="text-danger">*</span></label>
                                    <select class="form-select" wire:model.live="selectedGateway">
                                        <option value="">Select Gateway</option>
                                        @foreach($gateways as $g)
                                            <option value="{{ $g['id'] }}">{{ $g['gateway_name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedGateway') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Channel <span class="text-danger">*</span></label>
                                    <select class="form-select" wire:model="selectedChannel" @disabled(!$selectedGateway)>
                                        <option value="">Select Channel</option>
                                        @foreach($channels as $c)
                                            <option value="{{ $c['id'] }}">{{ $c['channel_name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedChannel') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">Parameters</label>
                                        <button type="button" class="btn btn-sm btn-primary" wire:click.live="addParameterRow">+ Add Row</button>
                                    </div>
                                    
                                    @foreach($parameters as $i => $row)
                                        <div class="row g-2 align-items-center mb-2" wire:key="param-{{ $i }}">
                                            <div class="col-md-5">
                                                
                                                <input type="text"
                                                    class="form-control"
                                                    placeholder="{{$i}}"
                                                    wire:model="parameters.{{ $i }}.name" value="{{$i}}">
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text"
                                                    class="form-control"
                                                    placeholder="{{ $row }}"
                                                    wire:model="parameters.{{ $row }}.value">
                                            </div>
                                            <div class="col-md-2 text-end">
                                                @if(count($parameters) > 1)
                                                    <button type="button" class="btn btn-outline-danger"
                                                            wire:click.live="removeParameterRow({{ $i }})">
                                                        Remove 
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif
    <script>
document.addEventListener('livewire:init', () => {
    Livewire.on('toast', (data) => {
        if (window.Swal) {
            Swal.fire({
                toast: true, position: 'top-end', icon: data.notify || 'success',
                title: data.message || 'Done', showConfirmButton: false, timer: 2000
            });
        } else {
            alert(data.message || 'Done');
        }
    });
});
</script>
<script>
    // Show modal when Livewire dispatches event
    window.addEventListener('show-parameter-modal', () => {
        const modal = new bootstrap.Modal(document.getElementById('parameterModal'));
        modal.show();
    });

    // Close modal after save
    window.addEventListener('close-parameter-modal', () => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('parameterModal'));
        modal.hide();
    });
</script>


</div>
</div>
