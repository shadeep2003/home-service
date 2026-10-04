(() => {
    const role = document.getElementById('role');
    const fields = document.getElementById('provider-fields');
    const update = () => {
        const provider = role.value === 'provider';
        fields.hidden = !provider;
        fields.querySelector('fieldset').disabled = !provider;
        ['phone', 'service_area'].forEach(name => { document.getElementById(name).required = provider; });
    };
    role.addEventListener('change', update);
    update();
})();
