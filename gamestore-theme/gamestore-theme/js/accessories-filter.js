document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.accessories-filters');
    if (!form) {
        return;
    }

    form.querySelectorAll('select').forEach(function (select) {
        select.addEventListener('change', function () {
            form.submit();
        });
    });
});
