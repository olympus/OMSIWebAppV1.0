<script>
    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({ options, params }) => {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            if (status && !params.status) {
                params.status = status;
            }
        });
    });
</script>
