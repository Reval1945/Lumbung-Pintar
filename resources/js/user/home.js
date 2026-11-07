import { showSuccess, showError, showLoading, closeLoading } from '../sweetalert';

const btnSwal = document.getElementById('btn-test-swal');
const btnApi = document.getElementById('btn-test-api');
const result = document.getElementById('api-result');

btnSwal?.addEventListener('click', () => {
    showSuccess('SweetAlert2 berjalan dengan baik!');
});

btnApi?.addEventListener('click', async () => {
    result.innerHTML = '<div class="spinner-border text-primary" role="status"></div>';
    showLoading('Menghubungi API...');

    try {
        const response = await fetch('/api/ping', {
            headers: { Accept: 'application/json' },
        });
        const json = await response.json();

        closeLoading();

        if (!response.ok || !json.success) {
            throw new Error(json.message || 'API gagal.');
        }

        result.innerHTML = '<div class="alert alert-success mb-2">API dan MySQL terhubung.</div><pre class="bg-white border rounded p-3 mb-0"></pre>';
        result.querySelector('pre').textContent = JSON.stringify(json.data, null, 2);
    } catch (error) {
        closeLoading();
        result.innerHTML = '<div class="alert alert-danger mb-0"></div>';
        result.querySelector('.alert').textContent = error.message;
        showError(error.message);
    }
});