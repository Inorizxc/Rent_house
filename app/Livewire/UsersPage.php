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

    /** id редактируемой строки: null = ничего, 0 = новая, >0 = существующая */
    public ?int $editingRowId = null;

    /** активная ячейка (имя поля), чтобы открыть конкретный input */
    public ?string $editingField = null;

    /** буфер данных редактируемой строки */
    public array $row = [
        'role_id'    => null,
        'name'       => '',
        'sename'     => '',
        'patronymic' => '',
        'birth_date' => null,
        'email'      => '',
        'phone'      => '',
        'card'       => '',
    ];

    /** новый пароль (хэшируем, текущее значение не показываем) */
    public string $passwordNew = '';

    public function updatingSearch(){ $this->resetPage(); }

    /* ---------- создание / редактирование ---------- */

    public function startCreate()
    {
        $this->editingRowId = 0; // новая строка
        $this->editingField = 'name';
        $this->row = [
            'role_id'    => null,
            'name'       => '',
            'sename'     => '',
            'patronymic' => '',
            'birth_date' => null,
            'email'      => '',
            'phone'      => '',
            'card'       => '',
        ];
        $this->passwordNew = '';
    }

    public function startEdit(int $userId)
    {
        $u = User::findOrFail($userId);

        $this->editingRowId = $u->user_id;
        $this->editingField = null;

        $this->row = [
            'role_id'    => $u->role_id,
            'name'       => $u->name,
            'sename'     => $u->sename,
            'patronymic' => $u->patronymic,
            'birth_date' => optional($u->birth_date)->format('Y-m-d'),
            'email'      => $u->email,
            'phone'      => $u->phone,
            'card'       => $u->card,
        ];
        $this->passwordNew = '';
    }

    public function setField(int $userId, string $field)
    {
        if ($this->editingRowId !== $userId) {
            $userId === 0 ? $this->startCreate() : $this->startEdit($userId);
        }
        $this->editingField = $field;
    }

    public function cancelEdit()
    {
        $this->editingRowId = null;
        $this->editingField = null;
        $this->reset('row','passwordNew');
    }

    public function saveRow()
    {
        $rules = [
            'row.role_id'    => ['required','integer','exists:roles,role_id'],
            'row.name'       => ['required','string','min:2'],
            'row.sename'     => ['nullable','string'],
            'row.patronymic' => ['nullable','string'],
            'row.birth_date' => ['nullable','date'],
            'row.email'      => [
                'required','email',
                Rule::unique('users','email')->ignore(
                    $this->editingRowId && $this->editingRowId>0 ? $this->editingRowId : null,
                    'user_id'
                )
            ],
            'row.phone'      => ['nullable','string'],
            'row.card'       => ['nullable','string'],
            'passwordNew'    => [$this->editingRowId === 0 ? 'required' : 'nullable','string','min:6'],
        ];
        $this->validate($rules);

        if ($this->editingRowId === 0) {
            // create
            $data = $this->row;
            $data['password'] = Hash::make($this->passwordNew);
            unset($data['user_id']); // автоинкремент
            User::create($data);
            session()->flash('ok','Пользователь создан');
        } else {
            // update
            $u = User::findOrFail($this->editingRowId);
            $u->fill($this->row);
            if ($this->passwordNew !== '') {
                $u->password = Hash::make($this->passwordNew);
            }
            $u->save();
            session()->flash('ok','Изменения сохранены');
        }

        $this->cancelEdit();
    }

    public function deleteUser(int $userId)
    {
        User::where('user_id',$userId)->delete();
        session()->flash('ok','Пользователь удалён');
        if ($this->editingRowId === $userId) $this->cancelEdit();
    }

    /* ---------- render ---------- */

    public function render()
    {
        $users = User::query()
            ->with('roles') // имя отношения в твоей модели
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
            ->paginate(15);

        $roles = Role::orderBy('name')->get(['role_id','name']);

        return view('livewire.users-page', compact('users','roles'))
            ->layout('components.layouts.app'); // или ->layout('layouts.app')
    }
}
