(() => {
    const helpers = window.travelGuide;
    if (!helpers) {
        return;
    }

    const editForm = document.getElementById('adminEditPostForm');
    const createUserForm = document.getElementById('adminCreateUserForm');

    createUserForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        helpers.clearErrors(createUserForm);
        const formData = new FormData(createUserForm);

        try {
            const payload = await helpers.postForm(createUserForm.action, formData);
            helpers.flashMessage(payload.message || 'User created.');
            window.location.reload();
        } catch (error) {
            if (error.errors) {
                helpers.showErrors(createUserForm, error.errors);
            } else {
                helpers.flashMessage(error.message || 'Could not create user.', 'error');
            }
        }
    });

    editForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        helpers.clearErrors(editForm);
        const formData = new FormData(editForm);

        try {
            const payload = await helpers.postForm(editForm.action, formData);
            helpers.flashMessage(payload.message || 'Post updated.');
            window.location.reload();
        } catch (error) {
            if (error.errors) {
                helpers.showErrors(editForm, error.errors);
            } else {
                helpers.flashMessage(error.message || 'Could not update post.', 'error');
            }
        }
    });

    document.addEventListener('click', async (event) => {
        const toggleButton = event.target.closest('.js-toggle-verification');
        if (toggleButton) {
            const formData = new FormData();
            formData.append('_csrf', helpers.csrfToken);
            formData.append('user_id', toggleButton.dataset.userId);
            formData.append('is_verified', toggleButton.dataset.isVerified);

            try {
                const payload = await helpers.postForm(helpers.routeUrl('api/admin/toggle-verification'), formData);
                helpers.flashMessage(payload.message || 'Verification updated.');
                window.location.reload();
            } catch (error) {
                helpers.flashMessage(error.message || 'Could not update verification.', 'error');
            }
            return;
        }

        const deleteUserButton = event.target.closest('.js-delete-user');
        if (deleteUserButton) {
            if (!window.confirm('Delete this user and all related records?')) {
                return;
            }

            const formData = new FormData();
            formData.append('_csrf', helpers.csrfToken);
            formData.append('user_id', deleteUserButton.dataset.userId);

            try {
                const payload = await helpers.postForm(helpers.routeUrl('api/admin/delete-user'), formData);
                helpers.flashMessage(payload.message || 'User deleted.');
                window.location.reload();
            } catch (error) {
                helpers.flashMessage(error.message || 'Could not delete user.', 'error');
            }
            return;
        }

        const approveButton = event.target.closest('.js-approve-request');
        if (approveButton) {
            const formData = new FormData();
            formData.append('_csrf', helpers.csrfToken);
            formData.append('request_id', approveButton.dataset.requestId);

            try {
                const payload = await helpers.postForm(helpers.routeUrl('api/admin/approve-request'), formData);
                helpers.flashMessage(payload.message || 'Request approved.');
                approveButton.closest('tr')?.remove();
            } catch (error) {
                helpers.flashMessage(error.message || 'Could not approve request.', 'error');
            }
            return;
        }

        const rejectButton = event.target.closest('.js-reject-request');
        if (rejectButton) {
            const formData = new FormData();
            formData.append('_csrf', helpers.csrfToken);
            formData.append('request_id', rejectButton.dataset.requestId);

            try {
                const payload = await helpers.postForm(helpers.routeUrl('api/admin/reject-request'), formData);
                helpers.flashMessage(payload.message || 'Request rejected.');
                rejectButton.closest('tr')?.remove();
            } catch (error) {
                helpers.flashMessage(error.message || 'Could not reject request.', 'error');
            }
            return;
        }

        const editButton = event.target.closest('.js-edit-post');
        if (editButton && editForm) {
            const post = helpers.decodePayload(editButton.dataset.post);
            editForm.post_id.value = post.id;
            editForm.title.value = post.title;
            editForm.country.value = post.country;
            editForm.genre.value = post.genre;
            editForm.cost_level.value = post.cost_level;
            editForm.status.value = post.status;
            editForm.short_history.value = post.short_history;
            editForm.country_representation.value = post.country_representation || '';
            editForm.travel_medium_info.value = post.travel_medium_info;
            window.scrollTo({ top: editForm.offsetTop - 80, behavior: 'smooth' });
            return;
        }

        const deletePostButton = event.target.closest('.js-delete-post');
        if (deletePostButton) {
            if (!window.confirm('Delete this post and its comments/wishlist entries?')) {
                return;
            }

            const formData = new FormData();
            formData.append('_csrf', helpers.csrfToken);
            formData.append('post_id', deletePostButton.dataset.postId);

            try {
                const payload = await helpers.postForm(helpers.routeUrl('api/admin/delete-post'), formData);
                helpers.flashMessage(payload.message || 'Post deleted.');
                window.location.reload();
            } catch (error) {
                helpers.flashMessage(error.message || 'Could not delete post.', 'error');
            }
            return;
        }

        const deleteCommentButton = event.target.closest('.js-delete-comment');
        if (!deleteCommentButton) {
            return;
        }

        const formData = new FormData();
        formData.append('_csrf', helpers.csrfToken);
        formData.append('comment_id', deleteCommentButton.dataset.commentId);

        try {
            const payload = await helpers.postForm(helpers.routeUrl('api/admin/delete-comment'), formData);
            helpers.flashMessage(payload.message || 'Comment deleted.');
            deleteCommentButton.closest('tr')?.remove();
        } catch (error) {
            helpers.flashMessage(error.message || 'Could not delete comment.', 'error');
        }
    });
})();
