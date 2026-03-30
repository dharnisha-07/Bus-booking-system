document.addEventListener('DOMContentLoaded', () => {
    // Sticky Navbar
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('sticky');
        } else {
            navbar.classList.add('sticky');
            if (window.location.pathname.includes('index.php') || window.location.pathname === '/' || window.location.pathname.endsWith('BusBookingSystem/')) {
               if(window.scrollY <= 50) navbar.classList.remove('sticky');
            }
        }
    });
    
    // Always make navbar sticky on non-index pages
    if (!(window.location.pathname.includes('index.php') || window.location.pathname === '/' || window.location.pathname.endsWith('BusBookingSystem/'))) {
        navbar.classList.add('sticky');
    }

    // Modal Logic
    const loginBtn = document.getElementById('loginBtn');
    const authModal = document.getElementById('authModal');
    const closeModal = document.querySelector('.close-modal');
    const authTabs = document.querySelectorAll('.auth-tab');
    const authContents = document.querySelectorAll('.auth-form-content');

    if (loginBtn) {
        loginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            authModal.classList.add('active');
        });
    }

    if (closeModal) {
        closeModal.addEventListener('click', () => {
            authModal.classList.remove('active');
        });
    }

    if (authModal) {
        authModal.addEventListener('click', (e) => {
            if (e.target === authModal) {
                authModal.classList.remove('active');
            }
        });
    }

    // Tab Switching
    authTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            authTabs.forEach(t => t.classList.remove('active'));
            authContents.forEach(c => c.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById(tab.dataset.target).classList.add('active');
        });
    });

    // Auth Form Submission
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const spinner = document.getElementById('spinnerOverlay');

    const handleAuth = async (form, action) => {
        if(!form) return;
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            spinner.style.display = 'flex';
            try {
                const response = await fetch(`auth.php?action=${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                spinner.style.display = 'none';
                
                if (result.success) {
                    window.location.href = result.redirect || 'index.php';
                } else {
                    alert(result.message);
                }
            } catch (error) {
                spinner.style.display = 'none';
                alert('An error occurred. Please try again.');
                console.error(error);
            }
        });
    }

    handleAuth(loginForm, 'login');
    handleAuth(registerForm, 'register');
});
