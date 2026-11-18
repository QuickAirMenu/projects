document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select[data-searchable]').forEach(function (select) {
        if (select.dataset.enhanced === '1') return;
        select.dataset.enhanced = '1';

        var options = Array.from(select.options).map(function (option) {
            return { value: option.value, text: option.text.trim(), disabled: option.disabled };
        });

        var selectedOption = select.options[select.selectedIndex];
        var placeholder = (options[0] && options[0].value === '') ? options[0].text : (select.dataset.searchPlaceholder || '');
        var selectedText = (selectedOption && selectedOption.value !== '') ? selectedOption.text.trim() : '';

        select.classList.add('hidden');
        select.setAttribute('tabindex', '-1');

        var wrapper = document.createElement('div');
        wrapper.className = 'relative';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        var input = document.createElement('input');
        input.type = 'text';
        input.autocomplete = 'off';
        input.className = select.className;
        input.placeholder = placeholder;
        input.value = selectedText;
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-autocomplete', 'list');
        wrapper.appendChild(input);

        var dropdown = document.createElement('div');
        dropdown.className = 'hidden absolute z-50 mt-1 w-full max-h-64 overflow-auto rounded-xl border border-gray-200 bg-white shadow-lg';
        wrapper.appendChild(dropdown);

        var visible = [];
        var activeIndex = -1;

        function open() {
            dropdown.classList.remove('hidden');
            input.setAttribute('aria-expanded', 'true');
        }

        function close() {
            dropdown.classList.add('hidden');
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        }

        function choose(option) {
            select.value = option.value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            input.value = option.value === '' ? '' : option.text;
            close();
            input.blur();
        }

        function render() {
            var query = input.value.trim().toLowerCase();
            var current = select.value;

            visible = options.filter(function (option) {
                if (option.disabled) return false;
                if (option.value === '') return query === '';
                return query === '' || option.text.toLowerCase().indexOf(query) !== -1;
            });

            dropdown.innerHTML = '';
            activeIndex = -1;

            if (visible.length === 0) {
                var empty = document.createElement('div');
                empty.className = 'px-3 py-2 text-sm text-gray-400';
                empty.textContent = input.dataset.emptyLabel || '-';
                dropdown.appendChild(empty);
                return;
            }

            visible.forEach(function (option) {
                var item = document.createElement('button');
                item.type = 'button';
                item.className = 'block w-full px-3 py-2 text-start text-sm hover:bg-gray-50' + (option.value === current ? ' bg-blue-50 text-blue-700' : ' text-gray-700');
                item.textContent = option.text;

                item.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                    choose(option);
                });

                dropdown.appendChild(item);
            });
        }

        function highlight(step) {
            var items = dropdown.querySelectorAll('button');
            if (items.length === 0) return;
            if (activeIndex >= 0 && items[activeIndex]) items[activeIndex].classList.remove('bg-gray-100');
            activeIndex = (activeIndex + step + items.length) % items.length;
            items[activeIndex].classList.add('bg-gray-100');
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        }

        input.addEventListener('focus', function () {
            render();
            open();
        });

        input.addEventListener('input', function () {
            render();
            open();
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (dropdown.classList.contains('hidden')) { render(); open(); }
                highlight(1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                highlight(-1);
            } else if (event.key === 'Enter') {
                if (!dropdown.classList.contains('hidden') && visible.length > 0) {
                    event.preventDefault();
                    choose(visible[activeIndex >= 0 ? activeIndex : 0]);
                }
            } else if (event.key === 'Escape') {
                close();
                input.value = selectedText;
            }
        });

        input.addEventListener('blur', function () {
            window.setTimeout(function () {
                close();
                var selected = select.options[select.selectedIndex];
                if (selected && selected.value !== '') {
                    selectedText = selected.text.trim();
                    input.value = selectedText;
                } else {
                    input.value = '';
                }
            }, 120);
        });
    });
});
