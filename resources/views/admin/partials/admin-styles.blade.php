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

