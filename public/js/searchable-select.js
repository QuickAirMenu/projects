document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select[data-searchable]').forEach(function (select) {
        var wrapper = document.createElement('div');
        wrapper.className = 'space-y-2';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        var input = document.createElement('input');
        input.type = 'search';
        input.className = select.className;
        input.placeholder = select.dataset.searchPlaceholder || 'Search...';
        input.setAttribute('autocomplete', 'off');
        wrapper.insertBefore(input, select);

        var options = Array.from(select.options).map(function (option) {
            return { value: option.value, text: option.text, selected: option.selected };
        });

        input.addEventListener('input', function () {
            var query = input.value.trim().toLowerCase();
            var current = select.value;

            select.innerHTML = '';

            options.forEach(function (option) {
                var matches = query === '' || option.text.toLowerCase().indexOf(query) !== -1;
                if (!matches) return;

                var el = document.createElement('option');
                el.value = option.value;
                el.text = option.text;
                if (option.value === current) el.selected = true;
                select.appendChild(el);
            });

            if (select.options.length === 0) {
                var empty = document.createElement('option');
                empty.value = '';
                empty.text = input.dataset.emptyLabel || '-';
                select.appendChild(empty);
            }
        });
    });
});
