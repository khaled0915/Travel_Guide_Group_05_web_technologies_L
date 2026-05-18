(() => {
	const metaBaseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
	const metaCsrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

	const baseUrl = metaBaseUrl.replace(/\/$/, '');

	const routeUrl = (route, params = {}) => {
		const searchParams = new URLSearchParams({ route });
		Object.entries(params).forEach(([key, value]) => {
			if (value !== undefined && value !== null && value !== '') {
				searchParams.set(key, String(value));
			}
		});

		return `${baseUrl || ''}/index.php?${searchParams.toString()}`;
	};

	const request = async (url, options = {}) => {
		const response = await fetch(url, {
			credentials: 'same-origin',
			...options,
			headers: {
				Accept: 'application/json',
				...(options.headers || {}),
			},
		});

		const contentType = response.headers.get('content-type') || '';
		const payload = contentType.includes('application/json')
			? await response.json()
			: { ok: response.ok, message: await response.text() };

		if (!response.ok || payload.ok === false) {
			const error = new Error(payload.message || 'Request failed.');
			if (payload.errors) {
				error.errors = payload.errors;
			}

			throw error;
		}

		return payload;
	};

	const postForm = (url, formData) => request(url, {
		method: 'POST',
		body: formData,
	});

	const clearErrors = (form) => {
		form.querySelectorAll('[data-error-for]').forEach((node) => {
			node.textContent = '';
		});
	};

	const showErrors = (form, errors = {}) => {
		Object.entries(errors).forEach(([field, message]) => {
			const target = form.querySelector(`[data-error-for="${field}"]`);
			if (target) {
				target.textContent = message;
			}
		});
	};

	const escapeHtml = (value) => {
		const text = String(value ?? '');
		const container = document.createElement('div');
		container.textContent = text;
		return container.innerHTML;
	};

	const encodePayload = (value) => btoa(unescape(encodeURIComponent(JSON.stringify(value))));
	const decodePayload = (value) => JSON.parse(decodeURIComponent(escape(atob(value))));

	const flashMessage = (message, type = 'success') => {
		const flashHost = document.querySelector('[data-client-flash]');
		if (!flashHost) {
			if (type === 'error') {
				console.error(message);
			} else {
				console.info(message);
			}
			return;
		}

		flashHost.textContent = message;
		flashHost.dataset.type = type;
		flashHost.hidden = false;
	};

	window.travelGuide = {
		baseUrl,
		csrfToken: metaCsrf,
		routeUrl,
		request,
		postForm,
		clearErrors,
		showErrors,
		escapeHtml,
		encodePayload,
		decodePayload,
		flashMessage,
	};
})();
