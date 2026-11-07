// CSS
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import '../css/app.css';

// Bootstrap 5 JS (dropdown, modal, offcanvas, collapse)
import * as bootstrap from 'bootstrap';

// Dibuat global agar bisa dipakai, misalnya: new bootstrap.Modal(...)
window.bootstrap = bootstrap;