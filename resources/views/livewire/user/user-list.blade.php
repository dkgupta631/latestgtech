<div>
    <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="/">GTech</a></li>
                            <li class="breadcrumb-item active">{{ __('messages.Users List') }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ __('messages.Users List') }}</h4>
                </div>
            </div>
        </div>     
        <!-- end page title --> 
        
        @php
            $me = Session::get('auth')->id;
            $roleMeta = [
                'Admin'    => ['class' => 'mm-role-admin', 'icon' => 'mdi-shield-account-outline', 'label' => __('messages.Admin')],
                'Agent'    => ['class' => 'mm-role-agent', 'icon' => 'mdi-account-tie-outline', 'label' => __('messages.Agent')],
                'Merchant' => ['class' => 'mm-role-merchant', 'icon' => 'mdi-store', 'label' => __('messages.Merchant')],
            ];
            $manageRoute = ['Agent' => route('agent.list'), 'Merchant' => route('merchant.list')];
            $allTotal = $roleCounts->sum('total');
            $palette = ['#c39226', '#1b1b1f', '#8a6410', '#5a5d66', '#a97c18', '#3a3a40'];
        @endphp

        {{-- KPI tiles (click to filter by role) --}}
        <div class="mm-kpis">
            <button type="button" class="mm-kpi mm-kpi-btn {{ $roleFilter === '' ? 'is-active' : '' }}" wire:click="$set('roleFilter', '')">
                <span class="mm-kpi-icon mm-kpi-navy"><i class="mdi mdi-account-group-outline"></i></span>
                <div><small>{{ __('messages.All User') }}</small><b>{{ $allTotal }}</b>
                    <span class="mm-kpi-sub"><span class="mm-dot"></span>{{ $roleCounts->sum('active') }} {{ __('messages.Active') }}</span></div>
            </button>
            @foreach (['Admin' => 'mm-kpi-blue', 'Agent' => 'mm-kpi-teal', 'Merchant' => 'mm-kpi-amber'] as $role => $tone)
                <button type="button" class="mm-kpi mm-kpi-btn {{ $roleFilter === $role ? 'is-active' : '' }}" wire:click="$set('roleFilter', '{{ $role }}')">
                    <span class="mm-kpi-icon {{ $tone }}"><i class="mdi {{ $roleMeta[$role]['icon'] }}"></i></span>
                    <div><small>{{ $roleMeta[$role]['label'] }}</small><b>{{ $roleCounts[$role]->total ?? 0 }}</b>
                        <span class="mm-kpi-sub"><span class="mm-dot"></span>{{ (int) ($roleCounts[$role]->active ?? 0) }} {{ __('messages.Active') }}</span></div>
                </button>
            @endforeach
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card-box">
                    <div class="mm-filters">
                        <div class="mm-filter-fields">
                            <div class="mm-input-icon mm-filter-search">
                                <i class="mdi mdi-magnify"></i>
                                <input type="text" wire:model.live.debounce.400ms="search" class="form-control" placeholder="{{ __('messages.Search here') }}...">
                            </div>
                            <select wire:model.live="roleFilter" class="form-control custom-select mm-filter-status" aria-label="{{ __('messages.Role Name') }}">
                                <option value="">{{ __('messages.All') }} {{ __('messages.Role Name') }}</option>
                                @foreach ($roleMeta as $role => $meta)
                                    <option value="{{ $role }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                            <select wire:model.live="perPage" class="form-control custom-select mm-filter-perpage" aria-label="{{ __('messages.Show') }}">
                                @foreach ([10, 25, 50] as $n)
                                    <option value="{{ $n }}">{{ $n }} / {{ __('messages.Show') }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="openAddModal" class="btn btn-success mm-filter-export">
                                <i class="mdi mdi-account-plus-outline"></i> {{ __('messages.Add User') }}
                            </button>
                        </div>
                    </div>

                    <div class="mm-loading" wire:loading.flex wire:target="search, roleFilter, perPage, gotoPage, nextPage, previousPage">
                        <span class="mm-spin mm-spin-blue"></span> {{ __('messages.Updating...') }}
                    </div>

                    <div class="table-responsive mm-table-scroll">
                        <table class="table mm-txn-table mm-user-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.Name') }}</th>
                                    <th>{{ __('messages.Email') }}</th>
                                    <th>{{ __('messages.Mobile') }}</th>
                                    <th>{{ __('messages.Role Name') }}</th>
                                    <th>{{ __('messages.Status') }}</th>
                                    <th>{{ __('messages.Created Time') }}</th>
                                    <th class="text-right">{{ __('messages.Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($usersrecords as $row)
                                    @php
                                        $meta = $roleMeta[$row->role_name] ?? ['class' => 'mm-role-agent', 'icon' => 'mdi-account-outline', 'label' => $row->role_name];
                                        $initials = collect(preg_split('/\s+/', trim($row->name ?: $row->user_name)))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                                        $isMe = $row->id == $me;
                                    @endphp
                                    <tr wire:key="row-{{ $row->id }}">
                                        <td>
                                            <div class="mm-user">
                                                <span class="mm-avatar" style="background: {{ $palette[$row->id % count($palette)] }}">{{ $initials }}</span>
                                                <div>
                                                    <b>{{ ucfirst($row->name) }} @if ($isMe)<span class="mm-you">{{ __('messages.You') }}</span>@endif</b>
                                                    <small>{{ '@' . $row->user_name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $row->email }}</td>
                                        <td class="mm-nowrap">{{ $row->mobile_number ?: '—' }}</td>
                                        <td><span class="mm-role {{ $meta['class'] }}"><i class="mdi {{ $meta['icon'] }}"></i> {{ $meta['label'] }}</span></td>
                                        <td>
                                            <span class="mm-status {{ $row->status ? 'mm-status-success' : 'mm-status-failed' }}">
                                                <i class="mdi {{ $row->status ? 'mdi-check-circle' : 'mdi-minus-circle' }}"></i>
                                                {{ $row->status ? __('messages.Active') : __('messages.Inactive') }}
                                            </span>
                                        </td>
                                        <td class="mm-nowrap">{{ $row->created_at ? strtoupper($row->created_at->format('dMY')) : '' }}</td>
                                        <td class="text-right">
                                            @if ($row->role_name == 'Admin')
                                                <div class="mm-row-actions">
                                                    <label class="switch mb-0" title="{{ $isMe ? __('messages.You cannot disable your own account.') : __('messages.Status') }}">
                                                        <input type="checkbox" wire:click="toggleStatus({{ $row->id }})" {{ $row->status ? 'checked' : '' }} @disabled($isMe)>
                                                        <span class="slider"></span>
                                                    </label>
                                                    <button type="button" class="mm-icon-btn mm-icon-edit" title="{{ __('messages.Edit User') }}" wire:click.prevent="editConfirmationFun({{ $row->id }})">
                                                        <i class="mdi mdi-pencil-outline"></i>
                                                    </button>
                                                    <button type="button" class="mm-icon-btn mm-icon-delete" title="{{ $isMe ? __('messages.You cannot delete your own account.') : __('messages.Delete') }}" wire:click.prevent="deleteConfirmationFun({{ $row->id }})" @disabled($isMe)>
                                                        <i class="mdi mdi-trash-can-outline"></i>
                                                    </button>
                                                </div>
                                            @elseif (isset($manageRoute[$row->role_name]))
                                                <a href="{{ $manageRoute[$row->role_name] }}" class="mm-manage-link">
                                                    {{ __('messages.Manage') }} <i class="mdi mdi-arrow-right"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="mm-empty-row">
                                        <td colspan="7">
                                            <div class="mm-empty">
                                                <i class="mdi mdi-account-search-outline"></i>
                                                <b>{{ __('messages.Record not found') }}</b>
                                                <span>{{ __('messages.Try another search or role.') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($usersrecords->total())
                        <div class="mm-pager">
                            <span>{!! __('messages.Showing :from–:to of :total', ['from' => '<b>' . $usersrecords->firstItem(), 'to' => $usersrecords->lastItem() . '</b>', 'total' => '<b>' . $usersrecords->total() . '</b>']) !!}</span>
                            {{ $usersrecords->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- end row -->

    {{-- Add / Edit user --}}
    @if ($showModal)
        <div class="mm-modal-backdrop" x-data
             x-on:click.self="$refs.dialog.classList.remove('mm-shake'); void $refs.dialog.offsetWidth; $refs.dialog.classList.add('mm-shake')">
            <div class="mm-modal" x-ref="dialog" role="dialog" aria-modal="true" aria-labelledby="userModalTitle">
                <div class="mm-modal-head">
                    <div class="mm-qrm-title">
                        <span class="mm-qrm-title-icon"><i class="mdi {{ $isEditMode ? 'mdi-account-edit-outline' : 'mdi-account-plus-outline' }}"></i></span>
                        <div>
                            <b id="userModalTitle">{{ $isEditMode ? __('messages.Edit User') : __('messages.Add User') }}</b>
                            <small>{{ __('messages.Admin account for the payment gateway panel') }}</small>
                        </div>
                    </div>
                    <button type="button" class="mm-qrm-close" wire:click="closeModal" aria-label="{{ __('messages.Close') }}"><i class="mdi mdi-close"></i></button>
                </div>

                <form wire:submit.prevent="saveGatewayAccount" novalidate>
                    <div class="mm-modal-body">
                        <div class="mm-form-section">{{ __('messages.Profile') }}</div>
                        <div class="row">
                            <div class="col-md-6 mm-field">
                                <label for="u_name">{{ __('messages.Name') }} <span class="text-danger">*</span></label>
                                <div class="mm-input-icon"><i class="mdi mdi-account-outline"></i>
                                    <input type="text" id="u_name" class="form-control @error('name') is-invalid @enderror" wire:model.live.debounce.300ms="name" placeholder="{{ __('messages.Enter here') }}"></div>
                                @error('name') <label class="error">{{ $message }}</label> @enderror
                            </div>
                            <div class="col-md-6 mm-field">
                                <label for="u_user">{{ __('messages.Username') }} <span class="text-danger">*</span></label>
                                <div class="mm-input-icon"><i class="mdi mdi-at"></i>
                                    <input type="text" id="u_user" autocomplete="off" class="form-control @error('user_name') is-invalid @enderror" wire:model.live.debounce.300ms="user_name" placeholder="{{ __('messages.Enter here') }}" @disabled($isEditMode)></div>
                                @if ($isEditMode)<small class="mm-hint">{{ __('messages.Username cannot be changed.') }}</small>@endif
                                @error('user_name') <label class="error">{{ $message }}</label> @enderror
                            </div>
                            <div class="col-md-6 mm-field">
                                <label for="u_email">{{ __('messages.Email') }} <span class="text-danger">*</span></label>
                                <div class="mm-input-icon"><i class="mdi mdi-email-outline"></i>
                                    <input type="email" id="u_email" class="form-control @error('email') is-invalid @enderror" wire:model.live.debounce.300ms="email" placeholder="{{ __('messages.Enter email') }}"></div>
                                @error('email') <label class="error">{{ $message }}</label> @enderror
                            </div>
                            <div class="col-md-6 mm-field">
                                <label for="u_mobile">{{ __('messages.Mobile') }} <span class="text-danger">*</span></label>
                                <div class="mm-input-icon"><i class="mdi mdi-phone-outline"></i>
                                    <input type="tel" id="u_mobile" class="form-control @error('mobile_number') is-invalid @enderror" wire:model.live.debounce.300ms="mobile_number" placeholder="{{ __('messages.Enter Mobile Number') }}"></div>
                                @error('mobile_number') <label class="error">{{ $message }}</label> @enderror
                            </div>
                        </div>

                        <div class="mm-form-section">{{ __('messages.Password') }}
                            @if ($isEditMode)<small>{{ __('messages.Leave blank to keep the current password.') }}</small>@endif
                        </div>
                        <div class="row">
                            <div class="col-md-6 mm-field">
                                <label for="u_pass">{{ __('messages.Password') }} @unless ($isEditMode)<span class="text-danger">*</span>@endunless</label>
                                <div class="mm-input-icon"><i class="mdi mdi-lock-outline"></i>
                                    <input type="{{ $showPassword ? 'text' : 'password' }}" id="u_pass" autocomplete="new-password" class="form-control with-eye @error('password') is-invalid @enderror" wire:model.live.debounce.300ms="password" placeholder="{{ __('messages.Enter password') }}">
                                    <button type="button" class="mm-eye" wire:click="$toggle('showPassword')" aria-label="{{ $showPassword ? __('messages.Hide password') : __('messages.Show password') }}"><i class="mdi {{ $showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline' }}"></i></button>
                                </div>
                                @error('password') <label class="error">{{ $message }}</label> @enderror
                            </div>
                            <div class="col-md-6 mm-field">
                                <label for="u_pass2">{{ __('messages.Confirm Password') }} @unless ($isEditMode)<span class="text-danger">*</span>@endunless</label>
                                <div class="mm-input-icon"><i class="mdi mdi-lock-reset"></i>
                                    <input type="{{ $showPassword ? 'text' : 'password' }}" id="u_pass2" autocomplete="new-password" class="form-control @error('password_confirmation') is-invalid @enderror" wire:model.live.debounce.300ms="password_confirmation" placeholder="{{ __('messages.Enter Confirm Password') }}" @disabled(!$password)></div>
                                @error('password_confirmation') <label class="error">{{ $message }}</label> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mm-modal-foot">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">{{ __('messages.Close') }}</button>
                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled" wire:target="saveGatewayAccount">
                            <i class="mdi mdi-content-save-outline"></i> {{ $isEditMode ? __('messages.Update') : __('messages.Save') }}
                        </button>
                    </div>
                </form>
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
