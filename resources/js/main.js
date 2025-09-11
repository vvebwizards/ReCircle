// Ported from temp/main.js

// Mobile Navigation Toggle
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');

if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });

    // Close mobile menu when clicking on a link
    document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    }));
}

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (!href || href === '#') return;
        const isSamePageAnchor = href.startsWith('#');
        if (!isSamePageAnchor) return;
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// Navbar background on scroll
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    if (window.scrollY > 100) {
        navbar.style.background = 'rgba(28, 69, 50, 0.98)';
        navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.25)';
    } else {
        navbar.style.background = 'rgba(28, 69, 50, 0.95)';
        navbar.style.boxShadow = 'none';
    }
});

// Animated counter for statistics
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target.toLocaleString();
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start).toLocaleString();
        }
    }, 16);
}

// Intersection Observer for animations
const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        if (entry.target.classList.contains('impact-stats')) {
            const statNumbers = entry.target.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const target = parseInt(stat.getAttribute('data-target') || '0', 10);
                animateCounter(stat, target);
            });
        }
        if (entry.target.classList.contains('step-item') || entry.target.classList.contains('role-item')) {
            entry.target.style.opacity = '0';
            entry.target.style.transform = 'translateY(30px)';
            entry.target.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            setTimeout(() => {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }, 100);
        }
    });
}, observerOptions);

document.addEventListener('DOMContentLoaded', () => {
    const impactStats = document.querySelector('.impact-stats');
    if (impactStats) io.observe(impactStats);
    document.querySelectorAll('.step-item, .role-item').forEach(card => io.observe(card));
});

// Form submission handling
const contactForm = document.querySelector('.contact-form');
if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        if (!data.name || !data.email || !data.interest || !data.message) {
            alert('Please fill in all required fields.');
            return;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(String(data.email))) {
            alert('Please enter a valid email address.');
            return;
        }
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Joining Platform...';
        submitBtn.disabled = true;
        setTimeout(() => {
            alert("Welcome to ReCircle! We'll send you onboarding information soon.");
            this.reset();
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }, 1500);
    });
}

// Newsletter subscription
const newsletterForm = document.querySelector('.newsletter');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const emailInput = this.querySelector('input[type="email"]');
        const email = emailInput ? emailInput.value : '';
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address.');
            return;
        }
        const submitBtn = this.querySelector('button');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Subscribing...';
        submitBtn.disabled = true;
        setTimeout(() => {
            alert('Thank you for subscribing to our newsletter!');
            if (emailInput) emailInput.value = '';
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }, 1000);
    });
}

// Add loading ripple to buttons
document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');
        this.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
    });
});

// Ripple effect styles
document.head.insertAdjacentHTML('beforeend', `<style>
.btn { position: relative; overflow: hidden; }
.ripple { position: absolute; border-radius: 50%; background: rgba(255, 255, 255, 0.3); transform: scale(0); animation: ripple-animation 0.6s linear; pointer-events: none; }
@keyframes ripple-animation { to { transform: scale(4); opacity: 0; } }
.btn-primary .ripple { background: rgba(255,255,255,0.22); }
.btn-secondary .ripple { background: rgba(45,90,39,0.12); }
</style>`);

// Parallax effect for hero section
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    document.querySelectorAll('.circular-graphic').forEach(element => {
        const speed = 0.5;
        element.style.transform = `translateY(${scrolled * speed}px)`;
    });
});

// Section fade-in animations
const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('section').forEach(section => sectionObserver.observe(section));

document.head.insertAdjacentHTML('beforeend', `<style>
section { opacity: 0; transform: translateY(30px); transition: opacity 0.8s ease, transform 0.8s ease; }
section.animate-in { opacity: 1; transform: translateY(0); }
.hero { opacity: 1; transform: none; }
</style>`);

// --- Simple client-side auth awareness for nav (demo-only) adapted to Laravel routes ---
document.addEventListener('DOMContentLoaded', () => {
    const menu = document.querySelector('.nav-menu');
    if (!menu) return;
    const routes = (window.appRoutes || {});
    const authUrl = routes.auth || '/auth';
    const dashUrl = routes.dashboard || '/dashboard';

    let authed = false;
    try { authed = localStorage.getItem('rc_auth') === 'true'; } catch {}
    let user = null;
    try { user = JSON.parse(localStorage.getItem('rc_user') || 'null'); } catch {}

    const findByHref = (href) => Array.from(menu.querySelectorAll('a')).find(a => a.getAttribute('href') === href);
    const signInItem = findByHref(authUrl);
    const dashItem = findByHref(dashUrl);
    const signOutItem = menu.querySelector('[data-signout]');
    const profileItem = menu.querySelector('.nav-item.profile');

    if (authed) {
        if (signInItem) signInItem.parentElement?.remove();
        if (!dashItem) {
            const li = document.createElement('li');
            li.className = 'nav-item';
            li.innerHTML = `<a href="${dashUrl}" class="nav-cta">Dashboard</a>`;
            menu.appendChild(li);
        }
        if (!profileItem) {
            const liP = document.createElement('li');
            liP.className = 'nav-item profile';
            liP.id = 'nav-profile';
            liP.style.marginLeft = '.25rem';
            liP.innerHTML = `
                <button class="avatar-btn" aria-haspopup="menu" aria-expanded="false" aria-label="Open profile menu">
                    <span class="avatar" id="nav-avatar">JD</span>
                    <i class="fa-solid fa-chevron-down chev"></i>
                </button>
                <ul class="profile-menu" role="menu" aria-label="Profile menu">
                    <li role="menuitem"><a href="#" class="profile-item"><i class="fa-regular fa-user"></i> Profile</a></li>
                    <li role="menuitem"><a href="#" class="profile-item"><i class="fa-solid fa-gear"></i> Settings</a></li>
                    <li role="menuitem"><a href="#" class="profile-item" data-signout><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a></li>
                </ul>`;
            const dashLi = findByHref(dashUrl)?.parentElement;
            if (dashLi && dashLi.nextSibling) dashLi.parentElement.insertBefore(liP, dashLi.nextSibling);
            else if (dashLi) dashLi.parentElement.appendChild(liP);
            else menu.appendChild(liP);
        }
        const initialsFrom = (u) => {
            if (!u) return 'JD';
            const guess = (u.name || u.email || 'User').toString().trim();
            const parts = guess.split(/[\s._-]+/).filter(Boolean);
            const initials = (parts[0]?.[0] || 'U') + (parts[1]?.[0] || (parts[0]?.[1] || 'R'));
            return initials.toUpperCase();
        };
        const avatarEl = document.getElementById('nav-avatar');
        if (avatarEl) avatarEl.textContent = initialsFrom(user);
        if (signOutItem && profileItem) signOutItem.parentElement?.remove();

        const getProfile = () => document.getElementById('nav-profile');
        const closeAnyProfile = () => {
            const p = getProfile();
            if (!p) return;
            const pmenu = p.querySelector('.profile-menu');
            const btn = p.querySelector('.avatar-btn');
            const chev = p.querySelector('.chev');
            pmenu?.classList.remove('open');
            btn?.setAttribute('aria-expanded', 'false');
            chev?.classList.remove('rot');
        };
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.avatar-btn');
            const profile = getProfile();
            if (btn && profile && profile.contains(btn)) {
                e.preventDefault();
                const pmenu = profile.querySelector('.profile-menu');
                const chev = profile.querySelector('.chev');
                const willOpen = !pmenu?.classList.contains('open');
                if (willOpen) { pmenu?.classList.add('open'); btn.setAttribute('aria-expanded', 'true'); chev?.classList.add('rot'); }
                else { pmenu?.classList.remove('open'); btn.setAttribute('aria-expanded', 'false'); chev?.classList.remove('rot'); }
                return;
            }
            if (profile && !profile.contains(e.target)) closeAnyProfile();
        });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAnyProfile(); });
        document.addEventListener('click', (e) => {
            const so = e.target.closest('[data-signout]');
            if (!so) return;
            e.preventDefault();
            try { localStorage.removeItem('rc_auth'); localStorage.removeItem('rc_user'); } catch {}
            window.location.replace(authUrl);
        });
    } else {
        if (!signInItem) {
            const li = document.createElement('li');
            li.className = 'nav-item';
            li.innerHTML = `<a href="${authUrl}" class="nav-cta" aria-label="Sign in">Sign In</a>`;
            menu.appendChild(li);
        }
        if (dashItem) dashItem.parentElement?.remove();
        if (signOutItem) signOutItem.parentElement?.remove();
        if (profileItem) profileItem.parentElement?.removeChild(profileItem);
    }

    if (!window.__rcSignoutWired) {
        document.addEventListener('click', (e) => {
            const so = e.target.closest('[data-signout]');
            if (!so) return;
            e.preventDefault();
            try { localStorage.removeItem('rc_auth'); localStorage.removeItem('rc_user'); } catch {}
            window.location.replace(authUrl);
        });
        window.__rcSignoutWired = true;
    }
});
