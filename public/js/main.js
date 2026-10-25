// Initialize Choices.js on page load
document.addEventListener('DOMContentLoaded', function () {
    initializeChoices();
});

// Reinitialize Choices.js after Livewire updates
document.addEventListener('livewire:load', function () {
    initializeChoices();

    Livewire.hook('message.processed', (message, component) => {
        initializeChoices();
    });
});

function initializeChoices() {
    const choicesElements = document.querySelectorAll('[data-choices="true"]:not(.choices__input)');

    choicesElements.forEach(function (element) {
        if (!element.classList.contains('choices__input')) {
            const isMultiple = element.hasAttribute('multiple');
            const searchEnabled = element.getAttribute('data-search') !== 'false';
            const removeButton = element.getAttribute('data-remove-item-button') === 'true';
            const placeholder = element.getAttribute('data-placeholder') || 'Select an option';

            const choices = new Choices(element, {
                searchEnabled: searchEnabled,
                removeItemButton: removeButton,
                placeholderValue: placeholder,
                shouldSort: false,
                itemSelectText: '',
                classNames: {
                    containerOuter: 'choices',
                    containerInner: 'choices__inner',
                    input: 'choices__input',
                    inputCloned: 'choices__input--cloned',
                    list: 'choices__list',
                    listItems: 'choices__list--multiple',
                    listSingle: 'choices__list--single',
                    listDropdown: 'choices__list--dropdown',
                    item: 'choices__item',
                    itemSelectable: 'choices__item--selectable',
                    itemDisabled: 'choices__item--disabled',
                    itemChoice: 'choices__item--choice',
                    placeholder: 'choices__placeholder',
                    group: 'choices__group',
                    groupHeading: 'choices__heading',
                    button: 'choices__button',
                    activeState: 'is-active',
                    focusState: 'is-focused',
                    openState: 'is-open',
                    disabledState: 'is-disabled',
                    highlightedState: 'is-highlighted',
                    selectedState: 'is-selected',
                    flippedState: 'is-flipped',
                    loadingState: 'is-loading',
                    noResults: 'has-no-results',
                    noChoices: 'has-no-choices'
                }
            });

            // Sync with Livewire
            element.addEventListener('change', function (event) {
                const livewireComponent = Livewire.find(element.closest('[wire\\:id]')?.getAttribute('wire:id'));
                if (livewireComponent) {
                    const modelName = element.getAttribute('wire:model.defer') || element.getAttribute('wire:model');
                    if (modelName) {
                        if (isMultiple) {
                            const selectedValues = Array.from(element.selectedOptions).map(option => option.value);
                            livewireComponent.set(modelName, selectedValues);
                        } else {
                            livewireComponent.set(modelName, element.value);
                        }
                    }
                }
            });
        }
    });
}

if (typeof livewire !== 'undefined') {
    livewire.on("showChoices", data => {
        const selectorId = "" + data[0] + "";
        const selectorData = data[1];
        const selectorOnchange = "" + data[2] + "";
        const selectorOptions = data[3];

        const element = document.querySelector(selectorId);
        if (!element) return;

        // Destroy existing Choices instance if it exists
        if (element.choices) {
            element.choices.destroy();
        }

        // Update options if provided
        if (selectorOptions != null) {
            element.innerHTML = '';

            selectorOptions.forEach(option => {
                const optionText = option.name ?? "";
                const optionId = option.id ?? "";
                const newOption = document.createElement('option');
                newOption.value = optionId;
                newOption.textContent = optionText;
                element.appendChild(newOption);
            });
        }

        // Initialize Choices
        const choices = new Choices(element, {
            searchEnabled: element.getAttribute('data-search') !== 'false',
            removeItemButton: element.getAttribute('data-remove-item-button') === 'true',
            shouldSort: false,
            itemSelectText: ''
        });

        // Set selected value
        if (selectorData) {
            choices.setChoiceByValue(selectorData);
        }

        // Handle change event
        element.addEventListener('change', function (e) {
            const data = element.value;
            livewire.emit(selectorOnchange, data);
        });
    });

    livewire.on("showSelect2", data => {
        const selectorId = "" + data[0] + "";
        const selectorData = data[1];
        const selectorOnchange = "" + data[2] + "";
        const selectorOptions = data[3];

        // Fallback to Choices if jQuery/Select2 is missing
        if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
            const element = document.querySelector(selectorId);
            if (!element) {
                return;
            }

            if (element.choices) {
                element.choices.destroy();
            }

            if (selectorOptions != null) {
                element.innerHTML = '';
                selectorOptions.forEach(opt => {
                    const newOption = document.createElement('option');
                    newOption.value = opt.id ?? "";
                    newOption.textContent = opt.name ?? "";
                    element.appendChild(newOption);
                });
            }

            const choices = new Choices(element, {
                searchEnabled: true,
                shouldSort: false,
                itemSelectText: ''
            });

            if (selectorData) {
                choices.setChoiceByValue(selectorData);
            }

            element.addEventListener('change', function () {
                livewire.emit(selectorOnchange, element.value);
            });
            return;
        }

        $(selectorId).select2({
            theme: "classic",
            width: "style"
        });

        if (selectorOptions != null) {
            $(selectorId).empty();
            selectorOptions.forEach(element => {
                var optionText = element.name ?? "";
                var optionId = element.id ?? "";
                var newOption = new Option(optionText, optionId, false, false);
                $(selectorId).append(newOption).trigger("change");
            });
            $(selectorId).val(null).trigger('change');
        }

        $(selectorId).off("change.livewire").on("change.livewire", function (e) {
            var data = $(this).select2("val");
            livewire.emit(selectorOnchange, data);
        });

        if (selectorData) {
            $(selectorId).val(selectorData).trigger("change");
        }


    });

    livewire.on("setQuillTextarea", data => {
        const selectorData = data[1];
        if (typeof quill !== 'undefined') {
            quill.root.innerHTML = selectorData;
        }
    });

    livewire.on("newTab", url => {
        window.open(url, '_blank').focus();
    });

    livewire.on("reloadPage", url => {
        window.location.reload();
    });
}

