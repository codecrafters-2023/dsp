document.addEventListener('DOMContentLoaded', function () {
    const hamburger = document.querySelector('.hamburger');
    const mobileMenu = document.querySelector('.mobile-menu');

    hamburger.addEventListener('click', function () {
        hamburger.classList.toggle('active');
        mobileMenu.classList.toggle('active');

        // Toggle body scroll when menu is open
        if (mobileMenu.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto';
        }
    });

    // Close menu when clicking on links
    const mobileLinks = document.querySelectorAll('.mobile-links .nav-link');
    mobileLinks.forEach(link => {
        link.addEventListener('click', function () {
            hamburger.classList.remove('active');
            mobileMenu.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    });
});


// Animation for service cards when they come into view
document.addEventListener('DOMContentLoaded', function () {
    const serviceCards = document.querySelectorAll('.service-card');

    // Initialize Intersection Observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.visibility = 'visible';
            }
        });
    }, { threshold: 0.1 });

    // Observe each service card
    serviceCards.forEach(card => {
        observer.observe(card);
    });

    // Add hover effect to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function () {
            this.classList.add('hover');
        });

        button.addEventListener('mouseleave', function () {
            this.classList.remove('hover');
        });
    });
});

// Animation trigger on scroll
document.addEventListener('DOMContentLoaded', function () {
    const animatedElements = document.querySelectorAll('.fade-in-up');

    // Initialize Intersection Observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                entry.target.style.opacity = 1;
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    // Observe each animated element
    animatedElements.forEach(element => {
        element.style.animationPlayState = 'paused';
        observer.observe(element);
    });
});

// Animation for testimonial cards when they come into view
document.addEventListener('DOMContentLoaded', function () {
    // Card fade-in animation is already handled by CSS animations
    // Adding hover effect enhancements with JS for better performance

    const cards = document.querySelectorAll('.testimonial-card');

    cards.forEach(card => {
        // Add mouse move effect
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const angleY = (x - centerX) / 25;
            const angleX = (centerY - y) / 25;

            card.style.transform = `perspective(1000px) rotateX(${angleX}deg) rotateY(${angleY}deg) scale(1.02)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)';
            setTimeout(() => {
                card.style.transform = '';
            }, 100);
        });
    });
});

// FAQ accordion functionality
// document.addEventListener('DOMContentLoaded', function () {
//     const faqItems = document.querySelectorAll('.faq-item');

//     faqItems.forEach(item => {
//         const question = item.querySelector('.faq-question');

//         question.addEventListener('click', () => {
//             // Close all other items
//             faqItems.forEach(otherItem => {
//                 if (otherItem !== item) {
//                     otherItem.classList.remove('active');
//                 }
//             });

//             // Toggle current item
//             item.classList.toggle('active');
//         });
//     });
// });

// Add animation to form elements when they come into view
document.addEventListener('DOMContentLoaded', function () {
    const formGroups = document.querySelectorAll('.form-group');

    formGroups.forEach(group => {
        group.style.opacity = '0';
        group.style.transform = 'translateY(20px)';
    });

    setTimeout(() => {
        formGroups.forEach((group, index) => {
            setTimeout(() => {
                group.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                group.style.opacity = '1';
                group.style.transform = 'translateY(0)';
            }, 200 + (index * 100));
        });
    }, 500);
});

// Add animation to elements when they come into view
document.addEventListener('DOMContentLoaded', function () {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.footer-col').forEach(col => {
        observer.observe(col);
    });
});


// Simple fade-in animation on scroll for About
document.addEventListener('DOMContentLoaded', function () {
    const fadeElements = document.querySelectorAll('.fade-in');

    const fadeInOnScroll = function () {
        fadeElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const elementVisible = 150;

            if (elementTop < window.innerHeight - elementVisible) {
                element.style.opacity = 1;
                element.style.visibility = 'visible';
                element.style.transform = 'translateY(0)';
            }
        });
    };

    // Initialize elements as hidden
    fadeElements.forEach(element => {
        element.style.opacity = 0;
        element.style.visibility = 'hidden';
        element.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
    });

    // Run on initial load
    fadeInOnScroll();

    // Run on scroll
    window.addEventListener('scroll', fadeInOnScroll);
});


// FAQ Toggle Functionality
// document.querySelectorAll('.faq-question').forEach(question => {
//     question.addEventListener('click', () => {
//         const faqItem = question.parentElement;
//         faqItem.classList.toggle('active');
//     });
// });

// // Fade-in animation on scroll
// const fadeElements = document.querySelectorAll('.fade-in');

// const fadeInObserver = new IntersectionObserver((entries) => {
//     entries.forEach(entry => {
//         if (entry.isIntersecting) {
//             entry.target.style.opacity = 1;
//         }
//     });
// }, { threshold: 0.1 });

// fadeElements.forEach(element => {
//     fadeInObserver.observe(element);
// });


  // FAQ Accordion functionality
        document.querySelectorAll('.parcel-faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentNode;
                item.classList.toggle('active');
            });
        });

        // Form submission animation
        const form = document.querySelector('.parcel-contact-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitButton = document.querySelector('.parcel-form-submit');
                submitButton.innerHTML = 'Sending... <i class="fas fa-spinner fa-spin"></i>';
                submitButton.disabled = true;
                
                // Simulate form submission
                setTimeout(() => {
                    submitButton.innerHTML = 'Message Sent! <i class="fas fa-check"></i>';
                    submitButton.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                    
                    // Reset form after success
                    setTimeout(() => {
                        form.reset();
                        submitButton.innerHTML = 'Send Message <i class="fas fa-paper-plane"></i>';
                        submitButton.style.background = 'linear-gradient(135deg, var(--primary), var(--primary-dark))';
                        submitButton.disabled = false;
                    }, 2000);
                }, 1500);
            });
        }

        // Add fade-in animation to elements when they enter viewport
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('parcel-fade-in');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.parcel-fade-in').forEach(el => {
            observer.observe(el);
        });