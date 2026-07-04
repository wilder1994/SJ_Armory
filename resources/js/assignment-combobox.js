function escapeAssignmentHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

export function normalizeAssignmentSearchText(value) {
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase();
}

function getOptionSearchText(option) {
    const custom = option.dataset.searchText || option.getAttribute('data-search-text') || '';

    return `${option.textContent.trim()} ${custom}`.trim();
}

function getOptionLabel(option) {
    return option.dataset.label || option.textContent.trim();
}

function getOptionSubtitle(option) {
    return option.dataset.subtitle || '';
}

/**
 * @param {HTMLElement} root Element with [data-assignment-combobox]
 */
export function initAssignmentCombobox(root) {
    const select = root.querySelector('[data-combobox-select]');
    const search = root.querySelector('[data-combobox-search]');
    const toggle = root.querySelector('[data-combobox-toggle]');
    const panel = root.querySelector('[data-combobox-panel]');

    if (!select || !search || !toggle || !panel) {
        return null;
    }

    const emptyMessage = root.dataset.emptyMessage || 'No se encontraron resultados.';
    const selectedBadge = root.dataset.selectedBadge || '';
    const clearOnMismatch = root.dataset.comboboxClearOnMismatch !== 'false';
    const availableOptions = Array.from(select.options).filter((option) => option.value !== '');

    const dispatchChange = () => {
        root.dispatchEvent(new CustomEvent('assignment-combobox:change', {
            bubbles: true,
            detail: {
                value: select.value,
                option: select.selectedOptions?.[0] ?? null,
            },
        }));
    };

    const closePanel = () => {
        panel.classList.add('hidden');
        search.setAttribute('aria-expanded', 'false');
    };

    const openPanel = () => {
        panel.classList.remove('hidden');
        search.setAttribute('aria-expanded', 'true');
    };

    const syncSearchFromSelection = () => {
        const option = select.selectedOptions?.[0];
        search.value = option?.value ? getOptionLabel(option) : '';
    };

    const renderOptions = (term = '') => {
        const normalizedTerm = normalizeAssignmentSearchText(term);
        const filtered = normalizedTerm === ''
            ? availableOptions
            : availableOptions.filter((option) => normalizeAssignmentSearchText(getOptionSearchText(option)).includes(normalizedTerm));

        if (filtered.length === 0) {
            panel.innerHTML = `<div class="px-3 py-2 text-sm text-slate-500">${escapeAssignmentHtml(emptyMessage)}</div>`;
            openPanel();
            return;
        }

        panel.innerHTML = filtered.map((option) => {
            const isSelected = select.value === option.value;
            const label = escapeAssignmentHtml(getOptionLabel(option));
            const subtitle = getOptionSubtitle(option);
            const selectedMarkup = isSelected && selectedBadge
                ? `<span class="shrink-0 text-xs font-semibold uppercase tracking-wide text-indigo-600">${escapeAssignmentHtml(selectedBadge)}</span>`
                : '';

            const subtitleMarkup = subtitle
                ? `<span class="block text-xs text-slate-500">${escapeAssignmentHtml(subtitle)}</span>`
                : '';

            return `
                <button
                    type="button"
                    class="flex w-full items-start justify-between gap-2 px-3 py-2 text-left text-sm transition hover:bg-slate-50 ${isSelected ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700'}"
                    data-combobox-value="${escapeAssignmentHtml(option.value)}"
                    role="option"
                    aria-selected="${isSelected ? 'true' : 'false'}"
                >
                    <span class="min-w-0">
                        <span class="block truncate">${label}</span>
                        ${subtitleMarkup}
                    </span>
                    ${selectedMarkup}
                </button>
            `;
        }).join('');

        openPanel();
    };

    const applySelection = (value) => {
        select.value = value;
        syncSearchFromSelection();
        dispatchChange();
        closePanel();
    };

    const syncSelectionFromSearchInput = () => {
        const trimmed = search.value.trim();
        const exactMatch = availableOptions.find((option) => getOptionLabel(option) === trimmed);

        if (exactMatch) {
            select.value = exactMatch.value;
        } else if (clearOnMismatch) {
            select.value = '';
        }

        dispatchChange();
    };

    search.addEventListener('focus', () => {
        renderOptions(search.value);
    });

    search.addEventListener('input', () => {
        syncSelectionFromSearchInput();
        renderOptions(search.value);
    });

    toggle.addEventListener('click', () => {
        if (panel.classList.contains('hidden')) {
            renderOptions(search.value);
            search.focus();
            return;
        }

        closePanel();
    });

    panel.addEventListener('click', (event) => {
        const optionButton = event.target.closest('[data-combobox-value]');
        if (!optionButton) {
            return;
        }

        applySelection(optionButton.dataset.comboboxValue);
    });

    select.addEventListener('change', dispatchChange);

    const onDocumentClick = (event) => {
        if (!root.contains(event.target)) {
            closePanel();
            syncSearchFromSelection();
        }
    };

    document.addEventListener('click', onDocumentClick);

    syncSearchFromSelection();

    return {
        select,
        search,
        syncSearchFromSelection,
        destroy() {
            document.removeEventListener('click', onDocumentClick);
        },
    };
}

export function initAssignmentComboboxes(scope = document) {
    const instances = [];

    scope.querySelectorAll('[data-assignment-combobox]').forEach((root) => {
        if (root.closest('[data-vest-form]')) {
            return;
        }

        const instance = initAssignmentCombobox(root);
        if (instance) {
            instances.push(instance);
        }
    });

    return instances;
}
