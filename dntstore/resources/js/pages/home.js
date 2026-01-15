export default function initHome() {
    // Check if we are on the home page
    if (!document.querySelector('body[data-page="home"]')) return;

    // Simple auto-slider for Hero if Alpine is not sufficient
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length > 1) {
        let current = 0;
        setInterval(() => {
            slides[current].classList.remove('active');
            current = (current + 1) % slides.length;
            slides[current].classList.add('active');
        }, 5000);
    }
}
