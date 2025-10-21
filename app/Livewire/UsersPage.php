<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsersPage extends Component
{
    use WithPagination;

    public $search = '';
    public $editingId = null;

    public $role_id;
    public $name;
    public $sename;
    public $patronymic;
    public $birth_date;
    public $email;
    public $password;
    public $phone;
    public $card;

    protected $rules = [
        'role_id' => 'required|exists:roles,id_role',
        'name' => 'required|string|min:2',
        'sename' => 'nullable|string',
        'patronymic' => 'nullable|string',
        'birth_date' => 'nullable|date',
        'email' => 'required|email',
        'password' => 'nullable|string|min:6',
        'phone' => 'nullable|string',
        'card' => 'nullable|string',
    ];

    public function updatingSearch() { $this->resetPage(); }

    public function create()
    {
        $this->resetInput();
        $this->dispatch('open-form');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->user_id;
        $this->role_id = $user->role_id;
        $this->name = $user->name;
        $this->sename = $user->sename;
        $this->patronymic = $user->patronymic;
        $this->birth_date = $user->birth_date;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->card = $user->card;
        $this->password = '';
        $this->dispatch('open-form');
    }

    public function save()
    {
        $data = $this->validate();

        // Если редактируем — не трогаем пароль, если пустой
        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            } else {
                unset($data['password']);
            }
            $user->update($data);
        } else {
            $data['password'] = Hash::make($this->password ?: '123456'); // дефолт
            User::create($data);
        }

        session()->flash('ok', 'Пользователь сохранён!');
        $this->dispatch('close-form');
    }

    public function delete($id)
    {
        User::where('user_id', $id)->delete();
        session()->flash('ok', 'Пользователь удалён!');
    }

    private function resetInput()
    {
        $this->editingId = null;
        $this->role_id = '';
        $this->name = '';
        $this->sename = '';
        $this->patronymic = '';
        $this->birth_date = '';
        $this->email = '';
        $this->password = '';
        $this->phone = '';
        $this->card = '';
    }

    public function render()
    {
        $query = User::query()
            ->with('roles')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });

        return view('livewire.users-page', [
            'users' => $query->paginate(10),
            'roles' => Role::all(),
        ])->layout('components.layouts.app');
    }
}
