// MOAUM front-end entry point (Module 1)
// Fonts are self-hosted via @fontsource (no Google Fonts CDN dependency).
import '@fontsource/inter/400.css';
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';
import '@fontsource/inter/700.css';
import '@fontsource/inter/800.css';
import '@fontsource/plus-jakarta-sans/700.css';
import '@fontsource/plus-jakarta-sans/800.css';

// Alpine.js — used only where it provides useful lightweight interaction
// (dropdowns, mega menu, mobile nav, dashboard sidebar toggles).
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
