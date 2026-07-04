import { initAssignmentCombobox } from './assignment-combobox';

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/**
 * @param {HTMLFormElement} form
 */
export function initVestForm(form) {
    const clientComboboxRoot = form.querySelector('[data-vest-client-combobox]');
    const clientSelect = form.querySelector('#vest-client-select');
    const workerComboboxRoot = form.querySelector('[data-vest-worker-combobox]');
    const workerSelect = form.querySelector('#vest-worker-select');
    const postComboboxRoot = form.querySelector('[data-vest-post-combobox]');
    const postSelect = form.querySelector('#vest-post-select');
    const deviceResponsibleInput = form.querySelector('[data-vest-device-responsible-input]');
    const deviceResponsibleDisplay = form.querySelector('[data-vest-device-responsible-display]');
    const modal = document.getElementById('vest-missing-responsible-modal');
    const modalClose = document.getElementById('vest-missing-responsible-modal-close');

    if (!clientComboboxRoot || !clientSelect || !workerComboboxRoot || !workerSelect || !postComboboxRoot || !postSelect) {
        return;
    }

    const lockDeviceResponsible = form.dataset.lockDeviceResponsible === '1';
    const fixedResponsibleName = form.dataset.fixedResponsibleName || '';
    const formOptionsUrl = form.dataset.formOptionsUrl || '';
    const emptyWorkerLabel = workerSelect.querySelector('option[value=""]')?.textContent?.trim() || 'Sin asignar';
    const emptyPostLabel = postSelect.querySelector('option[value=""]')?.textContent?.trim() || 'Sin puesto';
    const noResponsibleLabel = deviceResponsibleDisplay?.value?.trim()
        || deviceResponsibleDisplay?.getAttribute('value')?.trim()
        || 'Sin responsable asignado';

    /** @type {{ destroy?: () => void } | null} */
    let clientComboboxInstance = null;
    /** @type {{ destroy?: () => void } | null} */
    let workerComboboxInstance = null;
    /** @type {{ destroy?: () => void } | null} */
    let postComboboxInstance = null;

    const showModal = () => {
        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    const hideModal = () => {
        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    const setAssignmentFieldsEnabled = (enabled) => {
        [workerComboboxRoot, postComboboxRoot].forEach((root) => {
            root.querySelectorAll('[data-combobox-search], [data-combobox-toggle]').forEach((element) => {
                element.disabled = !enabled;
            });
        });

        workerSelect.disabled = !enabled;
        postSelect.disabled = !enabled;
    };

    const destroyWorkerPostComboboxes = () => {
        workerComboboxInstance?.destroy?.();
        postComboboxInstance?.destroy?.();
        workerComboboxInstance = null;
        postComboboxInstance = null;
    };

    const initVestComboboxes = () => {
        clientComboboxInstance?.destroy?.();
        destroyWorkerPostComboboxes();
        clientComboboxInstance = initAssignmentCombobox(clientComboboxRoot);
        workerComboboxInstance = initAssignmentCombobox(workerComboboxRoot);
        postComboboxInstance = initAssignmentCombobox(postComboboxRoot);
    };

    const initWorkerPostComboboxes = () => {
        workerComboboxInstance?.destroy?.();
        postComboboxInstance?.destroy?.();
        workerComboboxInstance = initAssignmentCombobox(workerComboboxRoot);
        postComboboxInstance = initAssignmentCombobox(postComboboxRoot);
    };

    const populateWorkerSelect = (workers, selectedValue = '') => {
        const options = [`<option value="">${escapeHtml(emptyWorkerLabel)}</option>`];

        workers.forEach((worker) => {
            const subtitle = `${worker.role_label || ''}${worker.document ? ` · ${worker.document}` : ''}`.trim();
            options.push(`
                <option
                    value="${escapeHtml(worker.id)}"
                    data-label="${escapeHtml(worker.name)}"
                    data-subtitle="${escapeHtml(subtitle)}"
                    data-search-text="${escapeHtml(worker.search_text || worker.name)}"
                    ${String(selectedValue) === String(worker.id) ? 'selected' : ''}
                >${escapeHtml(worker.name)}</option>
            `);
        });

        workerSelect.innerHTML = options.join('');
    };

    const populatePostSelect = (posts, selectedValue = '') => {
        const options = [`<option value="">${escapeHtml(emptyPostLabel)}</option>`];

        posts.forEach((post) => {
            options.push(`
                <option
                    value="${escapeHtml(post.id)}"
                    data-label="${escapeHtml(post.name)}"
                    data-subtitle="${escapeHtml(post.address || '')}"
                    data-search-text="${escapeHtml(post.search_text || post.name)}"
                    ${String(selectedValue) === String(post.id) ? 'selected' : ''}
                >${escapeHtml(post.name)}</option>
            `);
        });

        postSelect.innerHTML = options.join('');
    };

    const syncDeviceResponsible = () => {
        if (lockDeviceResponsible) {
            if (deviceResponsibleInput) {
                deviceResponsibleInput.value = fixedResponsibleName;
            }
            if (deviceResponsibleDisplay) {
                deviceResponsibleDisplay.value = fixedResponsibleName;
            }

            return true;
        }

        const option = clientSelect.selectedOptions?.[0];
        if (!option || !option.value) {
            if (deviceResponsibleInput) {
                deviceResponsibleInput.value = '';
            }
            if (deviceResponsibleDisplay) {
                deviceResponsibleDisplay.value = noResponsibleLabel;
            }

            return false;
        }

        const responsibleName = option.dataset.responsibleName || '';

        if (deviceResponsibleInput) {
            deviceResponsibleInput.value = responsibleName;
        }
        if (deviceResponsibleDisplay) {
            deviceResponsibleDisplay.value = responsibleName || noResponsibleLabel;
        }

        return responsibleName !== '';
    };

    const clearWorkerPostSelections = () => {
        populateWorkerSelect([]);
        populatePostSelect([]);
        initWorkerPostComboboxes();
    };

    const loadClientDependencies = async (clientId, preserveSelections = false) => {
        if (!clientId) {
            setAssignmentFieldsEnabled(false);
            clearWorkerPostSelections();
            syncDeviceResponsible();

            return;
        }

        if (!formOptionsUrl) {
            setAssignmentFieldsEnabled(true);
            syncDeviceResponsible();

            return;
        }

        const previousWorkerId = preserveSelections ? workerSelect.value : '';
        const previousPostId = preserveSelections ? postSelect.value : '';

        try {
            const response = await fetch(`${formOptionsUrl}?client_id=${encodeURIComponent(clientId)}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('form-options failed');
            }

            const data = await response.json();
            populateWorkerSelect(data.workers || [], previousWorkerId);
            populatePostSelect(data.posts || [], previousPostId);
            setAssignmentFieldsEnabled(true);
            initWorkerPostComboboxes();
        } catch {
            setAssignmentFieldsEnabled(false);
            clearWorkerPostSelections();
        }

        syncDeviceResponsible();
    };

    clientComboboxRoot.addEventListener('assignment-combobox:change', () => {
        const clientId = clientSelect.value;

        if (!syncDeviceResponsible() && clientId && !lockDeviceResponsible) {
            showModal();
        }

        loadClientDependencies(clientId, false);
    });

    form.addEventListener('submit', (event) => {
        if (!syncDeviceResponsible()) {
            event.preventDefault();
            showModal();
        }
    });

    modalClose?.addEventListener('click', hideModal);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            hideModal();
        }
    });

    initVestComboboxes();
    syncDeviceResponsible();

    if (clientSelect.value) {
        loadClientDependencies(clientSelect.value, true);
    } else {
        setAssignmentFieldsEnabled(false);
    }

    if (
        form.dataset.showMissingResponsibleModal === '1'
        || (form.dataset.clientError || '').includes('Primero debe realizar la asignación del responsable.')
    ) {
        showModal();
    }
}
