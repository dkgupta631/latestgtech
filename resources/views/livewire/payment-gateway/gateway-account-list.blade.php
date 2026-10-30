<div>
    <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="/">GTech</a></li>
                            <li class="breadcrumb-item active">{{ __('messages.Gateway Account') }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ __('messages.Gateway Account') }}</h4>
                </div>
            </div>
        </div>     
        <!-- end page title --> 
        
        <div class="row">
            <div class="col-12">
                <div class="card-box">
                    <!-- Search + Per Page -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label>{{ __('messages.Show') }}</label>
                            <select wire:model.live="perPage" class="form-control">
                                <option value="2">2</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>{{ __('messages.Search') }}</label>
                            <input type="text" wire:model.live="search" class="form-control" placeholder="{{ __('messages.Search here') }}...">
                        </div>
                        <div class="col-md-5"></div>
                        <div class="col-md-2">
                            <a href="#" wire:click.prevent="openAddModal" class="btn btn-success waves-effect waves-light float-right mb-3">
                                <i class="fas fa-plus"></i> {{ __('messages.Add Gateway Account') }}
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive mm-table-scroll">
                        <table  class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                            <tr>
                                <th>{{ __('messages.Order Id') }}</th>
                                <th>{{ __('messages.Gateway Account') }}</th>     
                                <th>{{ __('messages.Payment Method') }} </th>
                                <th>{{ __('messages.URL') }} </th>
                                <th>{{ __('messages.Created Time') }}</th>
                                <th>{{ __('messages.Status') }}</th>
                                <th>{{ __('messages.Action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                                 @forelse ($gateways as $index => $row)
                                    <tr wire:key="row-{{ $row->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ ucfirst($row->gateway_name) ?? '' }}</td>
                                        <td>{{ ucfirst($row->payment_method) ?? '' }}</td>
                                        <td class="mm-wrap-any">{{ $row->website_url ?? '' }}</td>
                                        <td> {{ $row->created_at ? strtoupper($row->created_at->format('dMY')) : '' }}</td>
                                        <td>
                                            <label class="switch">
                                                <input type="checkbox" wire:click="toggleStatus({{ $row->id }})" {{ $row->status ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary" wire:key="edit-{{ $row->id }}" wire:click.prevent="editConfirmationFun({{ $row->id }})">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                            <button class="btn btn-danger waves-effect waves-light" wire:key="delete-{{ $row->id }}" wire:click.prevent="deleteConfirmationFun({{ $row->id }})">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td></td><td></td><td></td><td>{{ __('messages.Record not found') }}!</td><td></td><td></td></td><td>
                                    </tr>
                                @endforelse
                        
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                        <div class="mt-3">
                            <span class="h5">{{ $gateways->links() }}</span>
                        </div>
                    
                </div>
            </div>
        </div>
        <!-- end row -->
<!-- Modal -->
    @if($showModal)
    <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditMode ? __('messages.Edit Gateway Account') : __('messages.Add Gateway Account') }}</h5>
                    <button type="button" class="close" wire:click="closeModal">×</button>
                </div>
                <form wire:submit.prevent="saveGatewayAccount">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('messages.Gateway Name') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="gateway_name">
                            @error('gateway_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>{{ __('messages.Website URL') }}<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="website_url">
                            @error('website_url') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>{{ __('messages.Payment Method') }}<span class="text-danger">*</span></label>
                            <select wire:model="payment_method" class="form-control custom-select">
                                <option value="">--Select--</option>
                                <option value="QR">QR</option>
                                <option value="card">Card</option>
                            </select>
                            @error('payment_method') <span class="text-danger">{{ $message }}</span> @enderror
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">{{ $isEditMode ? __('messages.Update') : __('messages.Save') }}</button>
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">{{ __('messages.Close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

<script>
    // FOR DELETE RECORD CODE START
        document.addEventListener('DOMContentLoaded', function () {
              window.addEventListener('show-delete-confirmation', event => {
                  Swal.fire({
                      title: "{{ __('messages.Are you sure you want to delete this record?') }}",
                      text: "{{ __('messages.If you delete this, it will be gone forever') }}!",
                      icon: "warning",
                      showCancelButton: true,
                      confirmButtonColor: "#3085d6",
                      cancelButtonColor: "#d33",
                      confirmButtonText: "{{ __('messages.Yes') }}!",
                      cancelButtonText: "{{ __('messages.No') }}"
                  }).then((result) => {
                      if (result.isConfirmed) {
                          if (typeof Livewire !== 'undefined' && typeof Livewire.dispatch === 'function') {
                              Livewire.dispatch('callDeleteListner');
                          } else {
                              console.error('Livewire is not available or dispatch function is not defined.');
                          }
                      }
                  });
              });
          });
        window.addEventListener('recordDeleted', event => {
          Swal.fire(
              "{{ __('messages.Done') }}!",
              "{{ __('messages.Successfully deleted') }}!",
              'success'
            )
        });
    // FOR DELETE RECORD CODE END
    // FOR CHANGE STATUS CODE START
        window.addEventListener('status-changed', event => {
            // Swal.fire({
            //     icon: 'success',
            //     title: event.message,
            //     timer: 1500,
            //     showConfirmButton: true
            // });
            Swal.fire(
                "{{ __('messages.Done') }}!",
                "{{ __('messages.Status Changed Successfully!') }}.",
                'success'
                )
        });
    // FOR CHANGE STATUS CODE END
    
</script>            

            
</div>
