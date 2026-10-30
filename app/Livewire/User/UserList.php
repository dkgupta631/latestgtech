<?php
namespace App\Livewire\User;
use Livewire\Component;
use App\Models\Agent;
use App\Models\Merchant;
use App\Models\User;
use Livewire\WithDispatchesEvents;
use Livewire\WithPagination;
use Session;

class UserList extends Component
{
    use WithPagination;
    public $search = '';        // Searching
    public $perPage = 10;       // Records per page
    public $roleFilter = '';    // '' = all roles
    public $showPassword = false;
    protected $paginationTheme = 'bootstrap';
    
    // FOR EDIT AND ADD RECORD FUNCTIONALITY CODE START
    public $user_id, $name, $user_name, $email, $mobile_number, $password, $password_confirmation;
    public $showModal = false; // To toggle the popup
    public $isEditMode = false; // To detect if editing or adding

    /** Open modal for adding new record */
    public function openAddModal()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
        
    }

    /** Open modal for editing */
    public function editConfirmationFun($id)
    {
        $userdata = User::findOrFail($id);
        $this->user_id = $userdata->id;
        $this->name = $userdata->name;
        $this->user_name = $userdata->user_name;
        $this->email = $userdata->email;
        $this->mobile_number = $userdata->mobile_number;

        $this->isEditMode = true;
        $this->showModal = true;
    }

    /** Save or update record */
    public function saveGatewayAccount()
    {
        // no need to check unique at edit case code 
            $rules = [
                'name'          => 'required|string|max:255',
                'user_name'     => 'required|string|max:255|unique:users,user_name,' . $this->user_id,
                'email'         => 'required|email|max:255|unique:users,email,' . $this->user_id,
                'mobile_number' => 'required|numeric|digits_between:10,12',
            ];
        
        if ($this->isEditMode) {
            // Update case → only validate password if filled
            if (!empty($this->password) || !empty($this->password_confirmation)) {
                $rules['password'] = 'required|min:8|confirmed';
            }
        } else {
            // Create case → password is required
            $rules['password'] = 'required|min:8|confirmed';
        }
        $this->validate($rules);
        // dd(base64_encode($this->password));

         $userData = [
            'name' => $this->name,
            'user_name' => $this->user_name,
            'email' => $this->email,
            'mobile_number' => $this->mobile_number,
            'role_id' => '1',  //for merchant role
            'role_name' => 'Admin',
        ];

        // Only update password if it's filled
        if (!empty($this->password)) {
            $userData['password'] = base64_encode($this->password);
        }
        
        User::updateOrCreate(
            ['id' => $this->user_id],
            $userData
        );


        $this->getdata();
        $msg = $this->isEditMode  ? __('messages.User updated successfully!') : __('messages.User added successfully!');
        $this->dispatch('toast', message: $msg, notify:'success' ); 
        $this->closeModal();
    }

    /** Close modal */
    public function closeModal()
    {
        $this->showModal = false;
    }

    /** Reset input fields */
    private function resetInputFields()
    {
        $this->user_id = null;
        $this->name = '';
        $this->user_name = '';
        $this->email = '';
        $this->mobile_number = '';
        $this->password = '';
        $this->password_confirmation = '';
    }
    // FOR EDIT AND ADD RECORD FUNCTIONALITY CODE END


    public function getdata()
    {
        $query = User::query()
            ->when($this->roleFilter, fn ($q) => $q->where('role_name', $this->roleFilter))
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('user_name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('mobile_number', 'like', "%{$this->search}%");
            });

        return $query->orderBy('role_id', 'asc')
            ->paginate($this->perPage);
    }



    // FOR CHANGE STATUS CODE START
    // back to page 1 whenever a filter changes
    public function updatedSearch() { $this->resetPage(); }
    public function updatedRoleFilter() { $this->resetPage(); }
    public function updatedPerPage() { $this->resetPage(); }

    public function toggleStatus($UserId)
    {
        $User = User::find($UserId);
        // an admin cannot disable their own account
        if ($User && $User->id == Session::get('auth')->id) {
            $this->dispatch('toast', message: __('messages.You cannot disable your own account.'), notify: 'error');
            return;
        }
        if ($User) {
            $User->status = !$User->status;
            $User->save();
        
            // Emit an event to trigger SweetAlert in frontend
            $this->dispatch('status-changed');
        }
    }
    // FOR CHANGE STATUS CODE END
    // FOR DELETE RECORD CODE START
    public $record_id;
    protected $listeners = ['callDeleteListner' => 'delete'];
    public function deleteConfirmationFun($id){
        // an admin cannot delete their own account
        if ($id == Session::get('auth')->id) {
            $this->dispatch('toast', message: __('messages.You cannot delete your own account.'), notify: 'error');
            return;
        }
        $this->record_id = $id;
        $this->dispatch('show-delete-confirmation');
    }
    public function delete(){
      
         $data= User::find($this->record_id);
         if (!$data || $data->id == Session::get('auth')->id) return;
         $data->delete();
         $this->getdata();
         $this->dispatch('recordDeleted');
    }
    // FOR DELETE RECORD CODE END

    public function render()
    {
        return view('livewire.user.user-list', [
            'usersrecords' => $this->getData(),
            'roleCounts' => User::selectRaw('role_name, COUNT(*) as total, SUM(status = 1) as active')->groupBy('role_name')->get()->keyBy('role_name'),
        ]);
    }
    
}
