require('./bootstrap');

import 'alpinejs'
// jQuery bundled with app.js
window.$ = require('jquery');
window.jQuery = window.$;
window.select2 = require('select2');
window.Swal = require('sweetalert2');

// Choices.js - Better Select2 alternative
import Choices from 'choices.js';
window.Choices = Choices;
// FontAwesome now loaded from CDN
import 'livewire-sortable'

// Import intl-tel-input
import intlTelInput from 'intl-tel-input';
window.intlTelInput = intlTelInput;

// FilePond - import plugins first, then register
import * as FilePond from 'filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';

FilePond.registerPlugin(FilePondPluginFileValidateType);
FilePond.registerPlugin(FilePondPluginFileValidateSize);
FilePond.registerPlugin(FilePondPluginImagePreview);
window.FilePond = FilePond;

