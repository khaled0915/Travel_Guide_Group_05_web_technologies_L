(() => {
    const helpers = window.travelGuide;
    const form = document.getElementById('commentForm');
    const list = document.getElementById('commentList');
    if (!helpers || !list) {
        return;
    }

    const renderComment = (comment, canDelete = true) => `
        <article class="comment-card" data-comment-id="${comment.id}">
            <div class="meta-row">
                <div>
                    <strong>${helpers.escapeHtml(comment.commenter_name)}</strong><br>
                    <span class="small-label">${new Date(comment.created_at).toLocaleString()}</span>
                </div>
                ${canDelete ? `<button class="danger-button js-delete-comment" type="button" data-comment-id="${comment.id}">Delete</button>` : ''}
            </div>
            <p class="muted-text">${helpers.escapeHtml(comment.content)}</p>
        </article>
    `;

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        helpers.clearErrors(form);

        const errors = {};
        if (!form.display_name.value.trim()) {
            errors.display_name = 'Name is required.';
        }
        if (!form.content.value.trim()) {
            errors.content = 'Comment cannot be empty.';
        } else if (form.content.value.trim().length > 500) {
            errors.content = 'Comment must be at most 500 characters.';
        }

        if (Object.keys(errors).length > 0) {
            helpers.showErrors(form, errors);
            return;
        }

        const formData = new FormData(form);
        try {
            const payload = await helpers.postForm(form.action, formData);
            const emptyCard = list.querySelector('.comment-card .muted-text');
            if (emptyCard?.textContent?.includes('No comments yet')) {
                list.innerHTML = '';
            }
            list.insertAdjacentHTML('afterbegin', renderComment(payload.comment));
            form.reset();
            form.display_name.value = payload.comment.commenter_name;
            helpers.flashMessage(payload.message || 'Comment added.');
        } catch (error) {
            if (error.errors) {
                helpers.showErrors(form, error.errors);
            } else {
                helpers.flashMessage(error.message || 'Could not save comment.', 'error');
            }
        }
    });

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('.js-delete-comment');
        if (!button || button.closest('table')) {
            return;
        }

        const formData = new FormData();
        formData.append('_csrf', helpers.csrfToken);
        formData.append('comment_id', button.dataset.commentId);

        try {
            const payload = await helpers.postForm(helpers.routeUrl('api/comments/delete'), formData);
            list.querySelector(`[data-comment-id="${payload.comment_id}"]`)?.remove();
            helpers.flashMessage(payload.message || 'Comment deleted.');
        } catch (error) {
            helpers.flashMessage(error.message || 'Could not delete comment.', 'error');
        }
    });
})();
