<link rel="icon" href="{{ asset('images/resolveiq-mark.svg') }}" type="image/svg+xml">
<script>
    try {
        if (localStorage.getItem('resolveiq-theme') === 'dark') {
            document.documentElement.dataset.theme = 'dark';
        }
    } catch {}
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fonts
