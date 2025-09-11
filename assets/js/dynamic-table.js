document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-dynamic-table]').forEach(table => {
        const tableName = table.dataset.tableName;
        const columns = JSON.parse(table.dataset.columns);

        let state = {
            filters: {},
            sortCol: 'id',
            sortDir: 'ASC',
            page: 1,
            pageSize: 10
        };

        // --- Create UI Elements ---
        const wrapper = document.createElement('div');
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);

        const topControls = document.createElement('div');
        topControls.className = 'd-flex justify-content-between mb-2';
        wrapper.insertBefore(topControls, table);

        const pageSizeSelector = document.createElement('select');
        pageSizeSelector.className = 'form-select form-select-sm w-auto';
        [10, 20, 50, 100].forEach(size => {
            const option = document.createElement('option');
            option.value = size;
            option.textContent = `${size} per pagina`;
            pageSizeSelector.appendChild(option);
        });
        topControls.appendChild(pageSizeSelector);

        const paginationControls = document.createElement('div');
        paginationControls.className = 'd-flex justify-content-end align-items-center';
        topControls.appendChild(paginationControls);

        const tHead = table.querySelector('thead');
        const filterRow = document.createElement('tr');
        columns.forEach(col => {
            const th = document.createElement('th');
            if (col !== 'actions') {
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control form-control-sm';
                input.placeholder = `Filtra per ${col}`;
                input.dataset.filter = col;
                th.appendChild(input);
            }
            filterRow.appendChild(th);
        });
        tHead.appendChild(filterRow);


        // --- Functions ---
        const fetchData = async () => {
            const payload = {
                table: tableName,
                columns: columns,
                ...state
            };

            if (table.dataset.tableJoins) {
                payload.joins = JSON.parse(table.dataset.tableJoins);
            }
            if (table.dataset.tableSelects) {
                payload.selects = JSON.parse(table.dataset.tableSelects);
            }

            const response = await fetch('/handlers/search_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            renderTable(result.data);
            renderPagination(result.pagination);
        };

        const renderers = {
            statusBadge: (data) => {
                if (parseInt(data, 10)) {
                    return '<span class="badge bg-success">Abilitato</span>';
                }
                return '<span class="badge bg-secondary">Disabilitato</span>';
            }
        };

        const renderTable = (data) => {
            const tBody = table.querySelector('tbody');
            const customRenderers = table.dataset.tableRenderers ? JSON.parse(table.dataset.tableRenderers) : {};

            tBody.innerHTML = '';
            if (data.length === 0) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.colSpan = columns.length;
                td.className = 'text-center';
                td.textContent = 'Nessun dato trovato.';
                tr.appendChild(td);
                tBody.appendChild(tr);
                return;
            }

            data.forEach(row => {
                const tr = document.createElement('tr');
                columns.forEach(col => {
                    const td = document.createElement('td');
                    if (col === 'actions') {
                        // Standard buttons
                        let buttons = `
                            <a href="view.php?id=${row.id}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            <a href="edit.php?id=${row.id}" class="btn btn-sm btn-warning"><i class="fas fa-pencil-alt"></i></a>
                            <a href="delete.php?id=${row.id}" class="btn btn-sm btn-danger" onclick="return confirm('Sei sicuro?');"><i class="fas fa-trash"></i></a>
                        `;

                        // Custom buttons
                        if (table.dataset.tableCustomActions) {
                            const customActions = JSON.parse(table.dataset.tableCustomActions);
                            customActions.forEach(action => {
                                buttons += ` <a href="${action.href}${row.id}" class="btn btn-sm ${action.class}"><i class="fas ${action.icon}"></i></a>`;
                            });
                        }
                        td.innerHTML = buttons;
                    } else {
                        const rendererName = customRenderers[col];
                        if (rendererName && renderers[rendererName]) {
                            td.innerHTML = renderers[rendererName](row[col]);
                        } else {
                            td.textContent = row[col] || '';
                        }
                    }
                    tr.appendChild(td);
                });
                tBody.appendChild(tr);
            });
        };

        const renderPagination = (pagination) => {
            const { page, totalPages, totalRecords } = pagination;
            paginationControls.innerHTML = `
                <span class="me-3">Pagina ${page} di ${totalPages} (${totalRecords} record)</span>
                <button class="btn btn-sm btn-outline-secondary me-1" data-page="1" ${page === 1 ? 'disabled' : ''}><i class="fas fa-angle-double-left"></i></button>
                <button class="btn btn-sm btn-outline-secondary me-1" data-page="${page - 1}" ${page === 1 ? 'disabled' : ''}><i class="fas fa-angle-left"></i></button>
                <button class="btn btn-sm btn-outline-secondary me-1" data-page="${page + 1}" ${page === totalPages ? 'disabled' : ''}><i class="fas fa-angle-right"></i></button>
                <button class="btn btn-sm btn-outline-secondary" data-page="${totalPages}" ${page === totalPages ? 'disabled' : ''}><i class="fas fa-angle-double-right"></i></button>
            `;
        };

        // --- Event Listeners ---
        pageSizeSelector.addEventListener('change', (e) => {
            state.pageSize = parseInt(e.target.value, 10);
            state.page = 1;
            fetchData();
        });

        paginationControls.addEventListener('click', (e) => {
            const button = e.target.closest('button');
            if (button && button.dataset.page) {
                state.page = parseInt(button.dataset.page, 10);
                fetchData();
            }
        });

        tHead.addEventListener('click', e => {
            const th = e.target.closest('th');
            if (th && th.dataset.sort) {
                const col = th.dataset.sort;
                if (state.sortCol === col) {
                    state.sortDir = state.sortDir === 'ASC' ? 'DESC' : 'ASC';
                } else {
                    state.sortCol = col;
                    state.sortDir = 'ASC';
                }
                fetchData();
            }
        });

        filterRow.addEventListener('input', e => {
            if (e.target.dataset.filter) {
                state.filters[e.target.dataset.filter] = e.target.value;
                state.page = 1;
                // Simple debounce
                clearTimeout(e.target.debounceTimer);
                e.target.debounceTimer = setTimeout(() => {
                    fetchData();
                }, 300);
            }
        });

        // Add sortable indicators to headers
        tHead.querySelectorAll('tr:first-child th').forEach((th, index) => {
            const colName = columns[index];
            if (colName !== 'actions') {
                th.dataset.sort = colName;
                th.style.cursor = 'pointer';
                // Add icon span
                const icon = document.createElement('i');
                icon.className = 'fas fa-sort ms-2';
                th.appendChild(icon);
            }
        });

        // --- Initial Fetch ---
        fetchData();
    });
});
