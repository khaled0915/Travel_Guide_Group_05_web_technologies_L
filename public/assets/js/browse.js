(() => {
    const helpers = window.travelGuide;
    const form = document.getElementById('browseFilterForm');
    const grid = document.getElementById('browsePostGrid');
    if (!helpers || !form || !grid) {
        return;
    }

    const canWishlist = grid.dataset.canWishlist === '1';
    const wishlistPostIds = new Set(
        (grid.dataset.wishlistPostIds || '')
            .split(',')
            .map((value) => value.trim())
            .filter(Boolean)
            .map((value) => Number(value))
    );

    const renderCards = (posts) => {
        if (!posts.length) {
            grid.innerHTML = '<div class="empty-state"><p>No posts matched the current filters.</p></div>';
            return;
        }

        grid.innerHTML = posts.map((post) => `
            <article class="post-card" data-post-card="${post.id}">
                <div class="post-image">
                    <img src="${post.image_path ? `${helpers.baseUrl}/${post.image_path}` : `${helpers.baseUrl}/assets/img/placeholder.svg`}" alt="${helpers.escapeHtml(post.title)}">
                </div>
                <div class="card-tags">
                    <span class="tag">${helpers.escapeHtml(post.country)}</span>
                    <span class="tag">${helpers.escapeHtml(post.genre.charAt(0).toUpperCase() + post.genre.slice(1))}</span>
                    <span class="tag">${post.cost_level.toUpperCase()}</span>
                </div>
                <div>
                    <h3>${helpers.escapeHtml(post.title)}</h3>
                    <p class="muted-text">${helpers.escapeHtml(post.short_history.slice(0, 120))}${post.short_history.length > 120 ? '...' : ''}</p>
                </div>
                <div class="inline-actions">
                    <a class="primary-button" href="${helpers.routeUrl('browse/detail', { id: post.id })}">Read more</a>
                    ${
                        canWishlist
                            ? wishlistPostIds.has(Number(post.id))
                                ? `<button class="danger-button js-wishlist-remove" type="button" data-post-id="${post.id}">Remove wishlist</button>`
                                : `<button class="ghost-button js-wishlist-add" type="button" data-post-id="${post.id}">Save wishlist</button>`
                            : ''
                    }
                </div>
            </article>
        `).join('');
    };

    const applyFilters = async () => {
        const params = new URLSearchParams();
        params.set('route', 'api/browse/search');
        const formData = new FormData(form);
        formData.forEach((value, key) => {
            if (typeof value === 'string' && value.trim() !== '') {
                params.append(key, value);
            }
        });

        try {
            const payload = await helpers.request(`${helpers.baseUrl}/index.php?${params.toString()}`);
            renderCards(payload.posts || []);
        } catch (error) {
            helpers.flashMessage(error.message || 'Could not filter posts.', 'error');
        }
    };

    let timer;
    form.addEventListener('input', () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(applyFilters, 250);
    });
    form.addEventListener('change', applyFilters);
})();
