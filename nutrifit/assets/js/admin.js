// JavaScript spécifique au back-office
// Initialise DataTables sur toutes les tables avec la classe .admin-table

(function() {
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.DataTable === 'undefined') {
            console.warn('jQuery ou DataTables non chargé – vérifie les CDN');
            return;
        }
        jQuery('.admin-table').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json'
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50]
        });
    });
})();
