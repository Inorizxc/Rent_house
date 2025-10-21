<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersPage extends Component
{
    use WithPagination;

    public string $search = '';

    // Поля формы
    public ?int $editingId = null;
    public ?int $role_id = null;
    public string $name = '';
    public ?string $sename = null;
    public ?string $patronymic = null;
    public ?string $birth_date = null; // 'Y-m-d'
    public string $email = '';
    public string $password = '';
    public ?string $phone = null;
    public ?string $card = null;

    protected function rules()
    {
        return [
            'role_id'    => ['required','integer','exists:roles,role_id'],
            'name'       => ['required','string','min:2'],
            'sename'     => ['nullable','string'],
            'patronymic' => ['nullable','string'],
            'birth_date' => ['nullable','date'],
            'email'      => [
                'required','email',
                Rule::unique('users','email')->ignore($this->editingId, 'user_id')
            ],
            'password'   => [$this->editingId ? 'nullable' : 'required','string','min:6'],
            'phone'      => ['nullable','string'],
            'card'       => ['nullable','string'],
        ];
    }

    public function updatingSearch(){ $this->resetPage(); }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-form');
    }

    public function edit(int $id)
    {
        $u = User::findOrFail($id);
        $this->editingId  = $u->user_id;
        $this->role_id    = $u->role_id;
        $this->name       = $u->name;
        $this->sename     = $u->sename;
        $this->patronymic = $u->patronymic;
        $this->birth_date = optional($u->birth_date)->format('Y-m-d');
        $this->email      = $u->email;
        $this->password   = ''; // пароль не показываем
        $this->phone      = $u->phone;
        $this->card       = $u->card;

        $this->dispatch('open-form');
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->editingId) {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
            User::where('user_id', $this->editingId)->update($data);
        } else {
            $data['password'] = Hash::make($data['password']);
            // НЕ передаём user_id при создании — пусть БД автоинкрементит
            unset($data['user_id']);
            User::create($data);
        }

        session()->flash('ok','Пользователь сохранён');
        $this->dispatch('close-form');
        $this->resetForm();
    }

    public function delete(int $id)
    {
        User::where('user_id',$id)->delete();
        session()->flash('ok','Пользователь удалён');
        if ($this->editingId === $id) $this->resetForm();
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->role_id = null;
        $this->name = '';
        $this->sename = null;
        $this->patronymic = null;
        $this->birth_date = null;
        $this->email = '';
        $this->password = '';
        $this->phone = null;
        $this->card = null;
    }

    public function render()
    {
        $users = User::query()
            ->with('roles') // имя отношения в твоей модели = roles()
            ->when($this->search, function($q){
                $s = "%{$this->search}%";
                $q->where(function($w) use ($s){
                    $w->where('name','like',$s)
                      ->orWhere('sename','like',$s)
                      ->orWhere('email','like',$s)
                      ->orWhere('phone','like',$s);
                });
            })
            ->orderByDesc('user_id')
            ->paginate(12);

        $roles = Role::orderBy('name')->get(['role_id','name']);

        // если layout у тебя в resources/views/components/layouts/app.blade.php:
        return view('livewire.users-page', compact('users','roles'))
            ->layout('components.layouts.app');
        // если перенесёшь — замени на ->layout('layouts.app')
    }
}
