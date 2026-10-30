<div>
    <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="/">GTech</a></li>
                            <li class="breadcrumb-item active">{{ __('messages.Agent Management') }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ __('messages.Agent Management') }}</h4>
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
                                <i class="fas fa-plus"></i> {{ __('messages.Add agent') }}
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive mm-table-scroll">
                        <table  class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                            <tr>
                                <th>{{ __('messages.Order Id') }}</th>
                                <th>{{ __('messages.Agent Name') }}</th>     
                                <th>{{ __('messages.Agent Code') }} </th>
                                <th>{{ __('messages.Email') }} </th>
                                <th>{{ __('messages.Mobile') }} </th>
                                <th>{{ __('messages.Created Time') }}</th>
                                <th>{{ __('messages.Status') }}</th>
                                <th>{{ __('messages.Action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                                 @forelse ($gateways as $index => $row)
                                    <tr wire:key="row-{{ $row->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ ucfirst($row->name) ?? '' }}</td>
                                        <td>{{ ucfirst($row->user_name) ?? '' }}</td>
                                        <td>{{ $row->email ?? '' }}</td>
                                        <td>{{ $row->mobile_number ?? '' }}</td>
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
        <div class="modal fade show d-block li" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg"> {{-- Make modal larger --}}
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-center w-100">
                            {{ $isEditMode ? __('messages.Edit agent') : __('messages.Add agent') }}
                        </h5>
                        <button type="button" class="close" wire:click="closeModal">×</button>
                    </div>

                    <form wire:submit.prevent="saveGatewayAccount">
                        <div class="modal-body">
                            <div class="row">
                                <!-- Agent Name -->
                                <div class="col-md-6 mb-3">
                                    <label>{{ __('messages.Agent Name') }}<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model.live="name" placeholder="{{ __('messages.Enter Agent Name') }}">
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <!-- Agent Code -->
                                <div class="col-md-6 mb-3">
                                    <label>{{ __('messages.Agent Code') }}<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model.live="user_name" placeholder="{{ __('messages.Enter Agent Code') }}" {{ $isEditMode ? 'disabled' : '' }}>
                                   
                                </div>

                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label>{{ __('messages.Email') }}<span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" wire:model.live="email" placeholder="{{ __('messages.Enter email') }}">
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <!-- Username -->
                                <div class="col-md-6 mb-3">
                                    <label>{{ __('messages.Username') }}<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model.live="user_name" placeholder="{{ __('messages.Enter here') }}" {{ $isEditMode ? 'disabled' : '' }}>
                                    @error('user_name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <!-- Mobile Number -->
                                <div class="col-md-6 mb-3">
                                    <label>{{ __('messages.Mobile') }}<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model.live="mobile_number" placeholder="{{ __('messages.Enter Mobile Number') }}">
                                    @error('mobile_number') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <!-- Timezone -->
                                <div class="col-md-6 mb-3">
                                    <label>{{ __('messages.Timezone') }}<span class="text-danger">*</span></label>
                                    <select class="form-control" wire:model="timezone">
                                        <option value="">--{{ __('messages.Select') }}--</option>
                                        <option value="Asia/Kolkata">Asia/Kolkata</option>
                                        <option value="Asia/Bangkok">Asia/Bangkok</option>
                                        <option value="UTC">UTC</option>
                                        <!-- Add more options as needed -->
                                    </select>
                                    @error('timezone') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <!-- Password -->
                                <div class="col-md-6 mb-3">
                                    <label>{{ __('messages.Password') }}<span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" wire:model.live="password" placeholder="{{ __('messages.Enter password') }}">
                                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-6 mb-3">
                                    <label>{{ __('messages.Confirm Password') }}<span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" wire:model.live="password_confirmation" placeholder="{{ __('messages.Enter Confirm Password') }}"  @if(!$password) disabled @endif>
                                    @error('password_confirmation') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                {{ $isEditMode ? __('messages.Update') : __('messages.Save') }}
                            </button>
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">
                                {{ __('messages.Close') }}
                            </button>
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
            Swal.fire(
                "{{ __('messages.Done') }}!",
                "{{ __('messages.Status Changed Successfully!') }}.",
                'success'
                )
        });
    // FOR CHANGE STATUS CODE END
    
</script>            

            
</div>
