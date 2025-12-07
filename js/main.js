document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.clickable-card');
    cards.forEach(card => {
        card.addEventListener('click', function(event) {
            const tag = event.target.tagName.toLowerCase();
            if (tag !== 'a' && tag !== 'button' && tag !== 'input' && tag !== 'label') {
                const destination = card.dataset.href;
                if (destination) {
                    window.location.href = destination;
                }
            }
        });
    });

    const navToggle = document.getElementById('nav-toggle');
    const navLinks = document.getElementById('nav-links');
    if (navToggle && navLinks) {
        const MOBILE_BREAKPOINT = 768;
        let wasDesktop = window.innerWidth >= MOBILE_BREAKPOINT;

        const handleResize = () => {
            const isDesktop = window.innerWidth >= MOBILE_BREAKPOINT;
            if (isDesktop) {
                navLinks.classList.add('is-open');
                navToggle.setAttribute('aria-expanded', 'true');
            } else if (wasDesktop && !isDesktop) {
                navLinks.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
            } else {
                navToggle.setAttribute('aria-expanded', String(navLinks.classList.contains('is-open')));
            }

            wasDesktop = isDesktop;
        };

        handleResize();

        navToggle.addEventListener('click', () => {
            const isOpen = navLinks.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', String(isOpen));
        });

        window.addEventListener('resize', handleResize);

        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < MOBILE_BREAKPOINT) {
                    navLinks.classList.remove('is-open');
                    navToggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    const resultSummary = document.getElementById('result-summary');
    if (!resultSummary) {
        return;
    }

    const canvas = document.getElementById('result-chart');
    if (canvas && typeof canvas.getContext === 'function') {
        const ctx = canvas.getContext('2d');
        if (ctx) {
            const correct = parseInt(resultSummary.dataset.correct || '0', 10);
            const incorrect = parseInt(resultSummary.dataset.incorrect || '0', 10);
            const unanswered = parseInt(resultSummary.dataset.unanswered || '0', 10);
            const total = Math.max(correct + incorrect + unanswered, 1);

            const slices = [
                { value: correct, color: '#2ecc71' },
                { value: incorrect, color: '#e74c3c' },
                { value: unanswered, color: '#f1c40f' }
            ];

            const size = Math.min(canvas.width, canvas.height);
            const radius = Math.max((size / 2) - 10, 10);
            const centerX = canvas.width / 2;
            const centerY = canvas.height / 2;

            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#0b0f1a';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            let startAngle = -Math.PI / 2;
            slices.forEach(segment => {
                const sliceAngle = total === 0 ? 0 : (segment.value / total) * Math.PI * 2;
                ctx.beginPath();
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, startAngle, startAngle + sliceAngle, false);
                ctx.closePath();
                ctx.fillStyle = segment.color;
                ctx.fill();
                startAngle += sliceAngle;
            });

            ctx.lineWidth = 2;
            ctx.strokeStyle = '#15213a';
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
            ctx.stroke();

            const percent = total === 0 ? 0 : Math.round((correct / total) * 100);
            ctx.fillStyle = '#f8fafc';
            ctx.font = 'bold 22px OpenDyslexic, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(`${percent}%`, centerX, centerY);
        }
    }

    window.setTimeout(() => {
        resultSummary.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 150);
});
