function select2exData() {

  return {
    loadResult() {
      // livewire.emit('logout');
    },
    //prepare the select 2
    init(id) {
      const selectorId = "#" + id;
      
      // Fallback to Choices if jQuery/Select2 is missing
      if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
        const element = document.querySelector(selectorId);
        if (!element) return;

        if (element.choices) element.choices.destroy();

        element.choices = new Choices(element, {
          searchEnabled: true,
          shouldSort: false,
          itemSelectText: '',
          removeItemButton: element.hasAttribute('multiple')
        });
        return;
      }

      $(selectorId).select2({
        theme: "classic",
        width: "style"
      });
    },

  }
}

