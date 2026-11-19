// Front-end entry for Vite (CSS companion). Alpine is provided by Livewire 4.
// Theme + sidebar stores are registered in layouts/app.blade.php via alpine:init
// so they bind before Livewire starts Alpine (Vite modules are deferred).

const savedTheme = localStorage.getItem('sme-theme') || 'light';
document.documentElement.classList.toggle('dark', savedTheme === 'dark');
