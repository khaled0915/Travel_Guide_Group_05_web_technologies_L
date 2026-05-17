(() => {
    const helpers = window.travelGuide;
    const form = document.getElementById('scoutRequestForm');
    if (!helpers || !form) {
        return;
    }

    const tableBody = document.getElementById('scoutRequestRows');
    const resetButton = document.getElementById('resetScoutForm');
    const fields = ['title', 'short_history', 'country', 'country_representation', 'genre', 'cost_level', 'travel_medium_info'];

    const setFormData = (payload = {}) => {
        form.request_id.value = payload.id || payload.request_id || 0;
        form.original_post_id.value = payload.original_post_id || 0;
        fields.forEach((field) => {
            if (form[field]) {
                form[field].value = payload[field] || '';
            }
        });
    };

    const clearForm = () => {
        form.reset();
        helpers.clearErrors(form);
        form.request_id.value = 0;
        form.original_post_id.value = 0;
    };

    const renderRow = (request) => {
        const safePayload = helpers.encodePayload(request);
        const locked = request.status !== 'pending';
        const date = new Date(request.requested_at).toLocaleDateString();

        return `
            <tr data-request-row="${request.id}">
                <td><strong>${helpers.escapeHtml(request.title)}</strong><br><span class="small-label">${helpers.escapeHtml(request.country)} | ${helpers.escapeHtml(request.genre)}</span></td>
                <td><span class="tag ${request.status}">${request.status.charAt(0).toUpperCase() + request.status.slice(1)}</span></td>
                <td>${date}</td>
                <td>
                    <div class="inline-actions">
                        ${
                            locked
                                ? '<span class="small-label">Locked</span>'
                                : `
                                    <button class="ghost-button js-edit-request" type="button" data-request="${safePayload}">Edit</button>
                                    <button class="danger-button js-delete-request" type="button" data-request-id="${request.id}">Delete</button>
                                `
                        }
                    </div>
                </td>
            </tr>
        `;
    };

    const validate = () => {
        const errors = {};
        fields.forEach((field) => {
            if (!form[field]?.value.trim()) {
                errors[field] = 'This field is required.';
            }
        });
        return errors;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        helpers.clearErrors(form);

        const errors = validate();
        if (Object.keys(errors).length > 0) {
            helpers.showErrors(form, errors);
            return;
        }

        const formData = new FormData(form);
        try {
            const payload = await helpers.postForm(form.action, formData);
            const request = payload.request;

            if (tableBody) {
                const existingRow = tableBody.querySelector(`[data-request-row="${request.id}"]`);
                if (existingRow) {
                    existingRow.outerHTML = renderRow(request);
                } else {
                    if (tableBody.querySelector('td[colspan="4"]')) {
                        tableBody.innerHTML = '';
                    }
                    tableBody.insertAdjacentHTML('afterbegin', renderRow(request));
                }
            }

            helpers.flashMessage(payload.message || 'Scout request saved.');
            clearForm();
        } catch (error) {
            if (error.errors) {
                helpers.showErrors(form, error.errors);
            } else {
                helpers.flashMessage(error.message || 'Could not save request.', 'error');
            }
        }
    });

    resetButton?.addEventListener('click', clearForm);

    document.addEventListener('click', async (event) => {
        const editButton = event.target.closest('.js-edit-request');
        if (editButton) {
            setFormData(helpers.decodePayload(editButton.dataset.request));
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        const changeButton = event.target.closest('.js-request-change');
        if (changeButton) {
            const post = helpers.decodePayload(changeButton.dataset.post);
            setFormData({
                request_id: 0,
                original_post_id: post.id,
                title: post.title,
                short_history: post.short_history,
                country: post.country,
                country_representation: post.country_representation,
                genre: post.genre,
                cost_level: post.cost_level,
                travel_medium_info: post.travel_medium_info,
            });
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        const deleteButton = event.target.closest('.js-delete-request');
        if (!deleteButton) {
            return;
        }

        if (!window.confirm('Delete this pending request?')) {
            return;
        }

        const formData = new FormData();
        formData.append('_csrf', helpers.csrfToken);
        formData.append('request_id', deleteButton.dataset.requestId);

        try {
            const payload = await helpers.postForm(helpers.routeUrl('api/scout/delete'), formData);
            tableBody?.querySelector(`[data-request-row="${payload.request_id}"]`)?.remove();
            helpers.flashMessage(payload.message || 'Request deleted.');
            clearForm();
        } catch (error) {
            helpers.flashMessage(error.message || 'Could not delete request.', 'error');
        }
    });
})();
