(() => {
    const form = document.getElementById('costCalculatorForm');
    if (!form) {
        return;
    }

    const result = document.getElementById('costCalculatorResult');
    const travelersField = document.getElementById('travelers');
    const daysField = document.getElementById('days');
    const baseCost = Number(document.getElementById('base_cost')?.value || 0);
    const currency = document.getElementById('base_currency')?.value || 'USD';

    const updateEstimate = () => {
        const travelers = Number(travelersField.value);
        const days = Number(daysField.value);
        const errors = {};

        form.querySelectorAll('[data-error-for]').forEach((node) => {
            node.textContent = '';
        });

        if (!Number.isInteger(travelers) || travelers < 1 || travelers > 10) {
            errors.travelers = 'Travelers must be between 1 and 10.';
        }

        if (!Number.isInteger(days) || days < 1 || days > 30) {
            errors.days = 'Days must be between 1 and 30.';
        }

        Object.entries(errors).forEach(([field, message]) => {
            const target = form.querySelector(`[data-error-for="${field}"]`);
            if (target) {
                target.textContent = message;
            }
        });

        if (Object.keys(errors).length > 0) {
            return;
        }

        const total = baseCost * travelers * (days / 7);
        result.textContent = `Estimated total: ${currency} ${total.toFixed(2)}`;
    };

    form.addEventListener('input', updateEstimate);
    updateEstimate();
})();
