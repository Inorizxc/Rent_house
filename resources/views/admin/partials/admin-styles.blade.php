.admin-container {
    display: flex;
    min-height: calc(100vh - 57px);
    background: #f6f6f7;
}

.admin-sidebar {
    width: 280px;
    background: #ffffff;
    border-right: 1px solid #e5e5e5;
    position: fixed;
    left: 0;
    top: 57px;
    height: calc(100vh - 57px);
    overflow-y: auto;
    transition: transform 0.3s ease;
    z-index: 999;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.03);
}

.admin-sidebar.closed {
    transform: translateX(-100%);
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #e5e5e5;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1c1c1c;
}

.sidebar-toggle {
    background: none;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 14px;
    color: #333;
    transition: all 0.2s ease;
}

.sidebar-toggle:hover {
    background: #f2f2f2;
    border-color: #d0d0d0;
}

.table-list {
    padding: 10px 0;
}

.table-item {
    display: block;
    padding: 12px 20px;
    color: #333;
    text-decoration: none;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    font-size: 14px;
}

.table-item:hover {
    background: #f9fafb;
    border-left-color: #667eea;
    padding-left: 22px;
}

.table-item.active {
    background: linear-gradient(90deg, #f3f4f6 0%, #f9fafb 100%);
    border-left-color: #667eea;
    font-weight: 500;
    color: #667eea;
}

.admin-content {
    flex: 1;
    margin-left: 280px;
    padding: 24px;
    transition: margin-left 0.3s ease;
}

.admin-content.expanded {
    margin-left: 0;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.content-header h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #1c1c1c;
}

.mobile-sidebar-toggle {
    display: none;
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px 14px;
    cursor: pointer;
    font-size: 14px;
    color: #333;
    transition: all 0.2s ease;
}

.mobile-sidebar-toggle:hover {
    background: #f2f2f2;
    border-color: #d0d0d0;
}

.admin-card {
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.rows-per-page {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
}

.rows-per-page label {
    font-size: 14px;
    color: #6b7280;
}

.rows-per-page input {
    width: 80px;
    padding: 6px 10px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-success {
    background: #d1fae5;
    border: 1px solid #10b981;
    color: #065f46;
}

.alert-error {
    background: #fee2e2;
    border: 1px solid #ef4444;
    color: #991b1b;
}

.btn {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
    font-family: inherit;
    text-decoration: none;
    display: inline-block;
}

.btn-danger {
    background: #dc2626;
    color: #ffffff;
}

.btn-danger:hover {
    background: #b91c1c;
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-primary {
    background: #3b82f6;
    color: #ffffff;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-edit {
    background: #f59e0b;
    color: #ffffff;
    margin-right: 8px;
}

.btn-edit:hover {
    background: #d97706;
}

.table-wrapper {
    overflow-x: auto;
    overflow-y: visible;
    width: 100%;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 16px;
}

.table-wrapper::-webkit-scrollbar {
    height: 8px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 100%;
    white-space: nowrap;
}

.data-table th,
.data-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e5e5e5;
    white-space: nowrap;
}

.data-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
    position: sticky;
    top: 0;
    z-index: 10;
}

.data-table tbody tr:hover {
    background: #f9fafb;
}

.data-table td:last-child {
    position: sticky;
    right: 0;
    background: #ffffff;
    z-index: 5;
    box-shadow: -2px 0 4px rgba(0, 0, 0, 0.05);
}

.data-table tbody tr:hover td:last-child {
    background: #f9fafb;
}

.data-table th:last-child {
    position: sticky;
    right: 0;
    background: #f9fafb;
    z-index: 11;
    box-shadow: -2px 0 4px rgba(0, 0, 0, 0.05);
}

/* Модальное окно */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: #ffffff;
    margin: auto;
    padding: 0;
    border: 1px solid #e5e5e5;
    border-radius: 12px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e5e5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f9fafb;
    border-radius: 12px 12px 0 0;
}

.modal-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #1c1c1c;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    font-weight: 300;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: #e5e7eb;
    color: #374151;
}

.modal-body {
    padding: 24px;
}

.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #e5e5e5;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: #f9fafb;
    border-radius: 0 0 12px 12px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 10px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.field-info {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
}

.empty-state p {
    margin: 0;
    font-size: 16px;
}

@media (max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
    }

    .admin-sidebar.open {
        transform: translateX(0);
    }

    .admin-content {
        margin-left: 0;
    }

    .mobile-sidebar-toggle {
        display: block;
    }
}

