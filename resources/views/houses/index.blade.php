@extends('layout')

@section('title','Дома')

@section('style')
    .houses-page-wrapper {
        padding: 90px 24px 24px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Уведомления */
    .alert-success {
        margin-bottom: 20px;
        padding: 12px 16px;
        background: #d1fae5;
        border: 1px solid #10b981;
        border-radius: 8px;
        color: #065f46;
        font-size: 14px;
    }

    /* Поисковая форма */
    .search-section {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 24px;
    }

    @media (min-width: 640px) {
        .search-section {
            flex-direction: row;
            align-items: flex-end;
            justify-content: space-between;
        }
    }

    .search-controls {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .search-input {
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        width: 100%;
        max-width: 288px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .btn-search {
        padding: 10px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #ffffff;
        color: #333;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s;
    }

    .btn-search:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .btn-reset {
        padding: 10px 12px;
        border: none;
        background: transparent;
        color: #4f46e5;
        font-size: 14px;
        text-decoration: underline;
        cursor: pointer;
        transition: color 0.2s;
    }

    .btn-reset:hover {
        color: #4338ca;
    }

    .btn-add-house {
        padding: 10px 16px;
        border-radius: 8px;
        background: #4f46e5;
        color: #ffffff;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: background 0.2s, transform 0.1s;
    }

    .btn-add-house:hover {
        background: #4338ca;
        transform: translateY(-1px);
    }

    /* Сетка карточек */
    .houses-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }

    @media (min-width: 640px) {
        .houses-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .houses-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 1280px) {
        .houses-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    /* Карточка дома */
    .house-card {
        background: #ffffff;
        border: 1px solid #e2e2e5;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .house-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
    }

    .house-card > a {
        text-decoration: none;
        color: inherit;
        display: block;
        flex: 1;
    }

    .house-card > a:hover {
        text-decoration: none;
    }

    .house-card-image {
        width: 100%;
        aspect-ratio: 4 / 3;
        object-fit: cover;
        background: #f3f4f6;
        display: block;
    }

    .house-card-content {
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .house-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 12px;
    }

    .house-card-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2933;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
    }

    .house-card-badge {
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 4px;
        background: #fee2e2;
        color: #991b1b;
        white-space: nowrap;
    }

    .house-card-details {
        margin-top: 12px;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px 16px;
        font-size: 14px;
        color: #4b5563;
    }

    .house-card-details dt {
        font-size: 12px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .house-card-details dd {
        margin: 0;
        color: #111827;
    }

    .house-card-details .col-span-2 {
        grid-column: span 2;
    }

    .house-card-details .font-mono {
        font-family: 'Courier New', monospace;
        font-size: 12px;
    }

    .house-card-actions {
        margin-top: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }

    .btn-edit,
    .btn-delete {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #ffffff;
        color: #333;
        font-size: 14px;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s;
        display: inline-block;
    }

    .btn-edit:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .btn-delete {
        color: #dc2626;
        border-color: #fecaca;
    }

    .btn-delete:hover {
        background: #fee2e2;
        border-color: #fca5a5;
    }

    /* Пустое состояние */
    .houses-empty {
        grid-column: 1 / -1;
        text-align: center;
        color: #6b7280;
        padding: 40px 20px;
        font-size: 16px;
    }

    /* Пагинация */
    .pagination-wrapper {
        margin-top: 24px;
        display: flex;
        justify-content: center;
    }

    .pagination {
        display: flex;
        gap: 8px;
        align-items: center;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .pagination li {
        display: inline-block;
    }

    .pagination a,
    .pagination span {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #ffffff;
        color: #333;
        text-decoration: none;
        font-size: 14px;
        display: inline-block;
        transition: background 0.2s, border-color 0.2s;
    }

    .pagination a:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .pagination .active span,
    .pagination .active a {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #ffffff;
    }

    .pagination .disabled span,
    .pagination .disabled a {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }
@endsection

@section('main_content')
    <div class="houses-page-wrapper">
        <livewire:houses-page :search-input="$searchInput ?? ''" />
    </div>
@endsection
