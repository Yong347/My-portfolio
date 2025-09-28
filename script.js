// ---------- Tab Navigation ----------
function opentab(tabname, event) {
    const tablinks = document.querySelectorAll(".tab-links");
    const tabcontents = document.querySelectorAll(".tab-contents");

    // Reset active states
    tablinks.forEach(link => link.classList.remove("active-link"));
    tabcontents.forEach(content => content.classList.remove("active-tab"));

    // Set active tab
    event.currentTarget.classList.add("active-link");
    document.getElementById(tabname).classList.add("active-tab");
}

// ---------- Mobile Side Menu ----------
const sidemenu = document.getElementById("sidemenu");
const mobileMenuBtn = document.querySelector(".mobile-menu");

function toggleMenu() {
    // Toggle menu open/close
    if (sidemenu.style.right === "0px" || sidemenu.classList.contains("active")) {
        sidemenu.style.right = "-200px";
        sidemenu.classList.remove("active");
    } else {
        sidemenu.style.right = "0";
        sidemenu.classList.add("active");
    }
}
mobileMenuBtn.addEventListener('click', toggleMenu);

// Reset side menu on resize
window.addEventListener('resize', () => {
    if (window.innerWidth > 900) {
        sidemenu.style.right = "0";
    } else {
        sidemenu.style.right = "-200px";
    }
    sidemenu.classList.remove("active");
});

// ---------- Scroll Navigation Highlight ----------
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('section, div[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    const scrollPos = window.scrollY + 100;

    sections.forEach(section => {
        if (scrollPos >= section.offsetTop && scrollPos < section.offsetTop + section.offsetHeight) {
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${section.id}`) {
                    link.classList.add('active');
                }
            });
        }
    });
});

// ---------- Page Initialization ----------
document.addEventListener('DOMContentLoaded', function() {
    initializeParticles();
    initializeTypingEffect();
    setupContactForm();

    // Set side menu default based on screen size
    sidemenu.style.right = (window.innerWidth <= 900) ? "-200px" : "0";

    // Setup tab links dynamically
    document.querySelectorAll('.tab-links').forEach(link => {
        link.addEventListener('click', function(e) {
            const tabname = this.getAttribute('onclick').match(/'([^']+)'/)[1];
            opentab(tabname, e);
        });
    });
});

// ---------- Background Particles ----------
function initializeParticles() {
    const particlesContainer = document.querySelector('.particles');
    const particleCount = 50;
    for (let i = 0; i < particleCount; i++) createParticle(particlesContainer);
}

function createParticle(container) {
    const particle = document.createElement('div');
    particle.className = 'particle';

    // Random position, size, color
    particle.style.left = Math.random() * 100 + '%';
    particle.style.top = Math.random() * 100 + '%';
    particle.style.animationDelay = Math.random() * 6 + 's';

    const size = Math.random() * 3 + 1;
    particle.style.width = size + 'px';
    particle.style.height = size + 'px';

    const colors = ['#ff004f', '#00d4ff', '#ffffff'];
    particle.style.background = colors[Math.floor(Math.random() * colors.length)];
    particle.style.opacity = Math.random() * 0.8 + 0.2;

    container.appendChild(particle);
}

// ---------- Typing Effect ----------
const typewriter = document.getElementById('typewriter');
let messages = [
    "a dedicated UI/UX designer with a deep understanding of what it takes to create beautiful and functional digital experiences..",
    "Let's build something amazing together!"
];
let messageIndex = 0,
    charIndex = 0,
    isDeleting = false,
    typingSpeed = 50;

function initializeTypingEffect() {
    function type() {
        const currentMessage = messages[messageIndex];

        // Typing / deleting
        if (isDeleting) {
            typewriter.textContent = currentMessage.substring(0, charIndex - 1);
            charIndex--;
            typingSpeed = 30;
        } else {
            typewriter.textContent = currentMessage.substring(0, charIndex + 1);
            charIndex++;
            typingSpeed = 50;
        }

        // Pause before deleting
        if (!isDeleting && charIndex === currentMessage.length) {
            setTimeout(() => isDeleting = true, 2000);
        }

        // Move to next message
        if (isDeleting && charIndex === 0) {
            isDeleting = false;
            messageIndex = (messageIndex + 1) % messages.length;
            typingSpeed = 500;
        }

        setTimeout(type, typingSpeed);
    }
    setTimeout(type, 1500);
}

// Update messages dynamically
function updateTypingMessages(newMessages) {
    if (Array.isArray(newMessages) && newMessages.length > 0) {
        messages = newMessages;
        console.log('Typing messages updated:', messages);
    }
}

// ---------- Contact Form ----------
function setupContactForm() {
    const contactForm = document.querySelector('#contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(contactForm);

            // Send to server
            fetch('process_form.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === "success") {
                        showNotification("Message sent successfully! I'll get back to you soon.", "success");
                        contactForm.reset();
                    } else {
                        showNotification("There was an error sending your message. Please try again.", "error");
                    }
                })
                .catch(error => {
                    showNotification("Error: " + error, "error");
                });
        });
    }
}

// ---------- Notifications ----------
function showNotification(message, type = 'success') {
    const old = document.querySelector(".notification");
    if (old) old.remove();

    const note = document.createElement("div");
    note.className = `notification ${type}`;
    note.innerHTML = `
        <span>${message}</span>
        <button class="close-btn">×</button>
    `;
    document.body.appendChild(note);

    setTimeout(() => note.classList.add("show"), 50);

    // Close button
    note.querySelector(".close-btn").addEventListener("click", () => {
        note.classList.remove("show");
        setTimeout(() => note.remove(), 400);
    });

    // Auto remove
    setTimeout(() => {
        if (document.body.contains(note)) {
            note.classList.remove("show");
            setTimeout(() => note.remove(), 400);
        }
    }, 5000);
}

// ---------- Side Menu Bar ----------
document.addEventListener('DOMContentLoaded', function() {
    const sideMenuBar = document.getElementById('sideMenuBar');
    const menuToggle = document.querySelector('.menu-toggle');
    const menuToggleIcon = document.getElementById('menuToggleIcon');
    const navLinks = document.querySelectorAll('.nav-link');

    // Toggle sidebar open/close
    function toggleSideMenu() {
        sideMenuBar.classList.toggle('active');
        menuToggleIcon.classList.toggle('fa-bars');
        menuToggleIcon.classList.toggle('fa-times');
    }
    menuToggle.addEventListener('click', toggleSideMenu);

    // Collapse on link click (mobile only)
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 900 && sideMenuBar.classList.contains('active')) {
                toggleSideMenu();
            }
        });
    });

    // Reset menu on desktop resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 900) {
            sideMenuBar.classList.remove('active');
            menuToggleIcon.classList.remove('fa-times');
            menuToggleIcon.classList.add('fa-bars');
        }
    });

    // Highlight active link on scroll
    function setActiveLink() {
        const sections = document.querySelectorAll('section, div[id]');
        let current = '';
        sections.forEach(section => {
            if (scrollY >= section.offsetTop - 100) {
                current = section.id;
            }
        });
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    }
    window.addEventListener('scroll', setActiveLink);
});