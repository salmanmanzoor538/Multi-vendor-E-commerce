import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

const $ = window.jQuery || window.$;
if ($ && csrfToken) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
        },
    });
}
