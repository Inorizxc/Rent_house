/**
 * Модуль для фильтрации заказов
 */

// Глобальная функция для инициализации фильтров заказов
window.initOrdersFilters = function(container) {
    container = container || document;
    console.log('Filtering orders with:', 'address:');
    const ordersGrid = container.getElementById('orders-houses-grid');
    if (!ordersGrid) {
        console.log('Orders grid not found');
        return;
    }

    const roleCheckboxes = container.querySelectorAll('input[type="checkbox"][data-filter-role]');
    const statusCheckboxes = container.querySelectorAll('input[type="checkbox"][data-filter-status]');
    const orderCards = container.querySelectorAll('.orders-compact-card');

    if (!roleCheckboxes.length || !statusCheckboxes.length) {
        console.log('Filter checkboxes not found');
        return;
    }

    if (!orderCards.length) {
        console.log('Order cards not found');
        return;
    }

    console.log('Initializing filters:', roleCheckboxes.length, 'role checkboxes,', statusCheckboxes.length, 'status checkboxes,', orderCards.length, 'cards');

    // Функция для получения выбранных фильтров
    function getSelectedFilters() {
        const selectedRoles = [];
        const selectedStatuses = [];

        const currentRoleCheckboxes = container.querySelectorAll('input[type="checkbox"][data-filter-role]');
        const currentStatusCheckboxes = container.querySelectorAll('input[type="checkbox"][data-filter-status]');

        currentRoleCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selectedRoles.push(checkbox.dataset.filterRole);
            }
        });

        currentStatusCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selectedStatuses.push(checkbox.dataset.filterStatus);
            }
        });

        return {
            roles: selectedRoles.length > 0 ? selectedRoles : ['customer', 'owner'],
            statuses: selectedStatuses.length > 0 ? selectedStatuses : ['рассмотрение', 'обработка', 'завершено', 'отменено', 'возврат','ожидает возврата','предоплачено']
        };
    }

    // Функция для фильтрации заказов
    function filterOrders() {
        
        const cards = container.querySelectorAll('.orders-compact-card');
        const grid = container.getElementById('orders-houses-grid');
        if (!grid) return;

        const filters = getSelectedFilters();

        const nameInput = container.querySelector('#filter-name-input');
        //const nameInput = container.querySelector('#filter-address-input');
        //const addressInput = container.querySelector('#filter-address-input');
        const addressInput = container.querySelector('#order-address-input');
        const customerSelect = container.querySelector('#filter-customer-select');
        const ownerSelect = container.querySelector('#filter-owner-select');

        const nameFilter = (nameInput ? nameInput.value.trim().toLowerCase() : '');
        const addressFilter = (addressInput ? addressInput.value.trim().toLowerCase() : '');
        const customerFilter = (customerSelect ? customerSelect.value : '');
        const ownerFilter = (ownerSelect ? ownerSelect.value : '');

        let visibleCount = 0;

        cards.forEach(card => {
            const cardRole = (card.dataset.orderRole || '').toLowerCase();
            const cardStatus = (card.dataset.orderStatus || '').toLowerCase();
            const customerName = (card.dataset.customerName || '').toLowerCase();
            const ownerName = (card.dataset.ownerName || '').toLowerCase();
            const houseAddress = (card.dataset.houseAddress || '').toLowerCase();
            const cardCustomerId = card.dataset.customerId || '';
            const cardOwnerId = card.dataset.ownerId || '';

            const roleMatch = filters.roles.includes(cardRole);
            const statusMatch = filters.statuses.includes(cardStatus);
            const customerMatch = !customerFilter || cardCustomerId === customerFilter;
            const ownerMatch = !ownerFilter || cardOwnerId === ownerFilter;
            const nameMatch = !nameFilter ||
                customerName.includes(nameFilter) ||
                ownerName.includes(nameFilter);
            const addressMatch = !addressFilter ||
                houseAddress.includes(addressFilter);

            if (roleMatch && statusMatch && customerMatch && ownerMatch && nameMatch && addressMatch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        let emptyMessage = grid.querySelector('.orders-empty-message');
        if (visibleCount === 0) {
            if (!emptyMessage) {
                emptyMessage = document.createElement('div');
                emptyMessage.className = 'orders-empty-message';
                emptyMessage.textContent = 'Заказы с выбранными фильтрами не найдены.';
                grid.appendChild(emptyMessage);
            }
        } else {
            if (emptyMessage) {
                emptyMessage.remove();
            }
        }
    }

    // Используем делегирование событий
    const filtersContainer = container.querySelector('.orders-filters-card') || container;

    filtersContainer.addEventListener('change', function(e) {
        const checkbox = e.target;
        if (checkbox.type === 'checkbox' && (checkbox.dataset.filterRole || checkbox.dataset.filterStatus)) {
            filterOrders();
        }
    }, { passive: true });

    roleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            filterOrders();
        }, { passive: true });
    });

    statusCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            filterOrders();
        }, { passive: true });
    });

    const customerSelect = container.querySelector('#filter-customer-select');
    const ownerSelect = container.querySelector('#filter-owner-select');

    if (customerSelect) {
        customerSelect.addEventListener('change', function() {
            filterOrders();
        }, { passive: true });
    }

    if (ownerSelect) {
        ownerSelect.addEventListener('change', function() {
            filterOrders();
        }, { passive: true });
    }

    // Инициализируем автодополнение
    initAutocomplete(container, filterOrders);

    // Инициализируем фильтрацию при загрузке
    filterOrders();
};

// Функция для инициализации автодополнения
function initAutocomplete(container, filterOrdersFunc) {
    const nameInput = container.querySelector('#filter-name-input');
    const addressInput = container.querySelector('#filter-address-input');
    const nameDropdown = container.querySelector('#name-autocomplete');
    const addressDropdown = container.querySelector('#address-autocomplete');

    if (!nameInput || !addressInput) return;

    const orderCards = container.querySelectorAll('.orders-compact-card');
    const namesSet = new Set();
    const addressesSet = new Set();

    orderCards.forEach(card => {
        const customerName = (card.dataset.customerNameOriginal || card.dataset.customerName || '').trim();
        const ownerName = (card.dataset.ownerNameOriginal || card.dataset.ownerName || '').trim();
        const address = (card.dataset.houseAddressOriginal || card.dataset.houseAddress || '').trim();

        if (customerName) namesSet.add(customerName);
        if (ownerName) namesSet.add(ownerName);
        if (address) addressesSet.add(address);
    });

    const names = Array.from(namesSet).sort();
    const addresses = Array.from(addressesSet).sort();

    function showSuggestions(input, dropdown, items, filterValue) {
        const query = filterValue.toLowerCase().trim();

        if (query.length === 0) {
            dropdown.classList.remove('show');
            return;
        }

        const filtered = items.filter(item =>
            item.toLowerCase().includes(query)
        ).slice(0, 10);

        if (filtered.length === 0) {
            dropdown.classList.remove('show');
            return;
        }

        dropdown.innerHTML = '';
        filtered.forEach(item => {
            const itemEl = document.createElement('div');
            itemEl.className = 'orders-autocomplete-item';
            itemEl.textContent = item;
            itemEl.addEventListener('click', () => {
                input.value = item;
                dropdown.classList.remove('show');
                filterOrdersFunc();
            });
            dropdown.appendChild(itemEl);
        });

        dropdown.classList.add('show');
    }

    let nameHighlightIndex = -1;
    nameInput.addEventListener('input', function() {
        nameHighlightIndex = -1;
        showSuggestions(nameInput, nameDropdown, names, this.value);
        if (filterOrdersFunc) {
            filterOrdersFunc();
        }
    });

    nameInput.addEventListener('keydown', function(e) {
        const items = nameDropdown.querySelectorAll('.orders-autocomplete-item');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            nameHighlightIndex = Math.min(nameHighlightIndex + 1, items.length - 1);
            items.forEach((item, idx) => {
                item.classList.toggle('highlighted', idx === nameHighlightIndex);
            });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            nameHighlightIndex = Math.max(nameHighlightIndex - 1, -1);
            items.forEach((item, idx) => {
                item.classList.toggle('highlighted', idx === nameHighlightIndex);
            });
        } else if (e.key === 'Enter' && nameHighlightIndex >= 0) {
            e.preventDefault();
            items[nameHighlightIndex].click();
        } else if (e.key === 'Escape') {
            nameDropdown.classList.remove('show');
        }
    });

    let addressHighlightIndex = -1;
    addressInput.addEventListener('input', function() {
        addressHighlightIndex = -1;
        showSuggestions(addressInput, addressDropdown, addresses, this.value);
        if (filterOrdersFunc) {
            filterOrdersFunc();
        }
    });

    addressInput.addEventListener('keydown', function(e) {
        const items = addressDropdown.querySelectorAll('.orders-autocomplete-item');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            addressHighlightIndex = Math.min(addressHighlightIndex + 1, items.length - 1);
            items.forEach((item, idx) => {
                item.classList.toggle('highlighted', idx === addressHighlightIndex);
            });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            addressHighlightIndex = Math.max(addressHighlightIndex - 1, -1);
            items.forEach((item, idx) => {
                item.classList.toggle('highlighted', idx === addressHighlightIndex);
            });
        } else if (e.key === 'Enter' && addressHighlightIndex >= 0) {
            e.preventDefault();
            items[addressHighlightIndex].click();
        } else if (e.key === 'Escape') {
            addressDropdown.classList.remove('show');
        }
    });

    document.addEventListener('click', function(e) {
        if (!nameInput.contains(e.target) && !nameDropdown.contains(e.target)) {
            nameDropdown.classList.remove('show');
        }
        if (!addressInput.contains(e.target) && !addressDropdown.contains(e.target)) {
            addressDropdown.classList.remove('show');
        }
    });
}

// Автоинициализация
(function() {
    function tryInit() {
        const grid = document.getElementById('orders-houses-grid');
        if (grid && window.initOrdersFilters) {
            window.initOrdersFilters();
        }
    }

    tryInit();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(tryInit, 100);
        });
    } else {
        setTimeout(tryInit, 200);
    }

    setTimeout(tryInit, 500);
    setTimeout(tryInit, 1000);
})();

