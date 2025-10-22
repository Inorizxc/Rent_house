<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\House;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class HousesPage extends Component
{
    use WithPagination, WithFileUploads;

    
    public string $search = '';
    public string $searchInput = '';

    /** null – ничего, 0 – создаём, >0 – редактируем */
    public ?int $editingId = null;

    public $imageTmp = null; // временный аплоад

    public array $form = [
        'user_id'        => null,
        'price_id'       => null,
        'rent_type_id'   => null,
        'house_type_id'  => null,
        'calendar_id'    => null,
        'adress'         => '',
        'area'           => null,
        'is_deleted'     => 0,
        'lng'            => null,   // << было lnd
        'lat'            => null,
    ];

    /* -------- поиск по кнопке -------- */
    public function applySearch(): void
    {
        $this->search = $this->searchInput;
        $this->resetPage();
    }

    /* -------- создание -------- */
    public function startCreate(): void
    {
        $this->editingId = 0;
        $this->form = [
            'user_id'        => null,
            'price_id'       => null,
            'rent_type_id'   => null,
            'house_type_id'  => null,
            'calendar_id'    => null,
            'adress'         => '',
            'area'           => null,
            'is_deleted'     => 0,
            'lng'            => null,
            'lat'            => null,
        ];
        $this->imageTmp = null;
    }

    /* -------- редактирование -------- */
    public function startEdit(int $id): void
    {
        $h = House::findOrFail($id);
        $this->editingId = $h->house_id;
        $this->form = [
            'user_id'        => $h->user_id,
            'price_id'       => $h->price_id,
            'rent_type_id'   => $h->rent_type_id,
            'house_type_id'  => $h->house_type_id,
            'calendar_id'    => $h->calendar_id,
            'adress'         => $h->adress,
            'area'           => $h->area,
            'is_deleted'     => (int) $h->is_deleted,
            'lng'            => $h->lng, // << было lnd
            'lat'            => $h->lat,
        ];
        $this->imageTmp = null;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->reset('form', 'imageTmp');
    }

    /* -------- сохранение -------- */
    public function save(): void
    {
        $rules = [
            'form.user_id'        => ['nullable','integer','exists:users,user_id'],
            'form.price_id'       => ['nullable','integer','min:0'],
            'form.rent_type_id'   => ['nullable','integer'],
            'form.house_type_id'  => ['nullable','integer'],
            'form.calendar_id'    => ['nullable','integer'],
            'form.adress'         => ['required','string','min:3'],
            'form.area'           => ['nullable','numeric','min:0'],
            'form.is_deleted'     => ['nullable','integer','in:0,1'],
            'form.lng'            => ['nullable','numeric'],
            'form.lat'            => ['nullable','numeric'],
            'imageTmp'            => ['nullable','image','max:8192'], // ~8MB
        ];
        $this->validate($rules);

        // фильтруем форму только по реально существующим колонкам таблицы
        $columns = collect(Schema::getColumnListing('houses'))
            ->flip(); // key=>index
        $data = collect($this->form)->intersectByKeys($columns)->all();

        if ($this->editingId === 0) {
            $h = House::create($data);
        } else {
            $h = House::findOrFail($this->editingId);
            $h->fill($data)->save();
        }

        // аплоад изображения
        if ($this->imageTmp) {
            $ext  = strtolower($this->imageTmp->getClientOriginalExtension() ?: 'jpg');
            // удаляем прошлые файлы с другим расширением
            foreach (Storage::disk('public')->files('houses') as $f) {
                if (preg_match('/^houses\/'.$h->house_id.'\.(jpg|jpeg|png|webp|gif)$/i', $f)) {
                    Storage::disk('public')->delete($f);
                    
                }
            }
            $this->imageTmp->storeAs('houses', $h->house_id.'.'.$ext, 'public');
        }

        session()->flash('ok', $this->editingId === 0 ? 'Дом создан' : 'Изменения сохранены');
        $this->cancelEdit();
    }

    public function delete(int $id): void
    {
        $h = House::findOrFail($id);
        foreach (Storage::disk('public')->files('houses') as $f) {
            if (preg_match('/^houses\/'.$h->house_id.'\.(jpg|jpeg|png|webp|gif)$/i', $f)) {
                Storage::disk('public')->delete($f);
            }
        }
        $h->delete();
        session()->flash('ok', 'Дом удалён');
        if ($this->editingId === $id) $this->cancelEdit();
    }

    public function render()
    {
        $houses = House::query()
            ->with(['user','rent_type','house_type'])
            ->when($this->search, function($q){
                $s = "%{$this->search}%";
                $q->where(function($w) use ($s){
                    $w->where('adress','like',$s)
                      ->orWhere('area','like',$s);
                });
            })
            ->where(function($q){
                // если столбца is_deleted нет — не фильтруем
                if (Schema::hasColumn('houses','is_deleted')) {
                    $q->where('is_deleted','!=',1)->orWhereNull('is_deleted');
                }
            })
            ->orderByDesc('house_id')
            ->paginate(12);

        $users = User::orderBy('name')->get(['user_id','name']);
        $placeholder = asset('images/house-placeholder.jpg');

        return view('livewire.houses-page', compact('houses','users','placeholder'))
            ->layout('components.layouts.app');
    }
}
