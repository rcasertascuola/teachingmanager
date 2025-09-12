<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/dynamic-table.js"></script>
<script>
// Initialize Bootstrap tooltips using event delegation for dynamically added content
document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Tooltip(document.body, {
        selector: "[data-bs-toggle='tooltip']"
    });
});
</script>
</body>
</html>
