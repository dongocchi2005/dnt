import Alpine from 'alpinejs';

window.Alpine = Alpine;

import './components/admin-shell';
import './ui-cyber';  // Import chat widget globally

import './pages/blog';
import './pages/booking';
import './pages/clearance-checkout'

// New Architecture Imports
import initHeader from './components/header';
import initHome from './pages/home';
import initProducts from './pages/products';

// Initialize Logic
document.addEventListener('DOMContentLoaded', () => {
    initHeader();
    initHome();
    initProducts();
});

Alpine.start();
