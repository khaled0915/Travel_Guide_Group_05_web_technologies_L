(() => {
	const helpers = window.travelGuide;
	if (!helpers) {
		return;
	}

	const wishlistGrid = document.querySelector('[data-wishlist-grid]');
	const browseGrid = document.getElementById('browsePostGrid');

	const updateCardButton = (postId, isSaved) => {
		const selector = `[data-post-id="${postId}"]`;
		const button = document.querySelector(selector);
		if (!button) {
			return;
		}

		button.className = isSaved ? 'danger-button js-wishlist-remove' : 'ghost-button js-wishlist-add';
		button.textContent = isSaved ? 'Remove wishlist' : 'Save wishlist';
	};

	const removeWishlistCard = (postId) => {
		wishlistGrid?.querySelector(`[data-wishlist-item="${postId}"]`)?.remove();
		if (wishlistGrid && wishlistGrid.querySelectorAll('[data-wishlist-item]').length === 0) {
			wishlistGrid.innerHTML = '<div class="empty-state"><p>Your wishlist is empty. Browse approved posts and save the ones you like.</p></div>';
		}
	};

	document.addEventListener('click', async (event) => {
		const addButton = event.target.closest('.js-wishlist-add');
		if (addButton) {
			const postId = addButton.dataset.postId;
			const formData = new FormData();
			formData.append('_csrf', helpers.csrfToken);
			formData.append('post_id', postId);

			try {
				const payload = await helpers.postForm(helpers.routeUrl('api/wishlist/add'), formData);
				helpers.flashMessage(payload.message || 'Post added to wishlist.');
				updateCardButton(postId, true);
				if (browseGrid?.dataset.wishlistPostIds !== undefined) {
					const ids = new Set((browseGrid.dataset.wishlistPostIds || '').split(',').map((value) => value.trim()).filter(Boolean));
					ids.add(String(postId));
					browseGrid.dataset.wishlistPostIds = Array.from(ids).join(',');
				}
			} catch (error) {
				helpers.flashMessage(error.message || 'Could not add post to wishlist.', 'error');
			}
			return;
		}

		const removeButton = event.target.closest('.js-wishlist-remove');
		if (!removeButton) {
			return;
		}

		const postId = removeButton.dataset.postId;
		const formData = new FormData();
		formData.append('_csrf', helpers.csrfToken);
		formData.append('post_id', postId);

		try {
			const payload = await helpers.postForm(helpers.routeUrl('api/wishlist/remove'), formData);
			helpers.flashMessage(payload.message || 'Post removed from wishlist.');
			removeWishlistCard(postId);
			updateCardButton(postId, false);
			if (browseGrid?.dataset.wishlistPostIds !== undefined) {
				const ids = (browseGrid.dataset.wishlistPostIds || '')
					.split(',')
					.map((value) => value.trim())
					.filter(Boolean)
					.filter((value) => value !== String(postId));
				browseGrid.dataset.wishlistPostIds = ids.join(',');
			}
		} catch (error) {
			helpers.flashMessage(error.message || 'Could not remove post from wishlist.', 'error');
		}
	});
})();
