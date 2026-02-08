// script.js - KIND WOLF - Fonctions JavaScript
// ============================================

// ============================================
// VARIABLES GLOBALES
// ============================================
let cartCount = 0;

// ============================================
// INITIALISATION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Charger le compteur du panier
    updateCartCount();
    
    // Menu mobile
    initMobileMenu();
    
    // Initialiser les animations au scroll
    initScrollAnimations();
});

// ============================================
// MENU MOBILE
// ============================================
function initMobileMenu() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
}

// ============================================
// GESTION DU PANIER
// ============================================

// Ajouter au panier
function addToCart(productId) {
    const quantityInput = document.getElementById('quantity');
    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
    
    fetch(BASE_URL + '/api/cart_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            showNotification('Produit ajouté au panier', 'success');
        } else {
            showNotification('Erreur lors de l\'ajout au panier', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

// Mettre à jour le panier
function updateCart(productId, change) {
    // Récupérer la quantité actuelle
    const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
    if (!cartItem) return;
    
    const quantitySpan = cartItem.querySelector('.cart-item-quantity span');
    let currentQuantity = parseInt(quantitySpan.textContent);
    let newQuantity = currentQuantity + change;
    
    if (newQuantity < 1) {
        newQuantity = 0;
    }
    
    fetch(BASE_URL + '/api/cart_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update&product_id=${productId}&quantity=${newQuantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (newQuantity === 0) {
                cartItem.remove();
            }
            location.reload(); // Recharger pour mettre à jour les totaux
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la mise à jour', 'error');
    });
}

// Supprimer du panier
function removeFromCart(productId) {
    if (!confirm('Voulez-vous vraiment supprimer cet article ?')) {
        return;
    }
    
    fetch(BASE_URL + '/api/cart_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la suppression', 'error');
    });
}

// Mettre à jour le compteur du panier
function updateCartCount() {
    fetch(BASE_URL + '/api/cart_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_count'
    })
    .then(response => response.json())
    .then(data => {
        const cartCountElement = document.getElementById('cartCount');
        if (cartCountElement && data.count !== undefined) {
            cartCountElement.textContent = data.count;
            cartCount = data.count;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

// ============================================
// GESTION DE LA QUANTITÉ (Page Produit)
// ============================================
function increaseQuantity() {
    const quantityInput = document.getElementById('quantity');
    if (quantityInput) {
        const max = parseInt(quantityInput.getAttribute('max'));
        let value = parseInt(quantityInput.value);
        if (value < max) {
            quantityInput.value = value + 1;
        }
    }
}

function decreaseQuantity() {
    const quantityInput = document.getElementById('quantity');
    if (quantityInput) {
        let value = parseInt(quantityInput.value);
        if (value > 1) {
            quantityInput.value = value - 1;
        }
    }
}

// ============================================
// WISHLIST (Liste de souhaits)
// ============================================
function addToWishlist(productId) {
    fetch(BASE_URL + '/api/product_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add_to_wishlist&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Ajouté à la liste de souhaits', 'success');
        } else {
            if (data.message === 'Connexion requise') {
                window.location.href = BASE_URL + '/auth/login.php';
            } else {
                showNotification(data.message || 'Erreur', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

function removeFromWishlist(productId) {
    fetch(BASE_URL + '/api/product_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove_from_wishlist&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

// ============================================
// ANNULATION DE COMMANDE
// ============================================
function cancelOrder(orderId) {
    if (!confirm('Êtes-vous sûr de vouloir annuler cette commande ? Cette action est irréversible.')) {
        return;
    }
    
    window.location.href = `${BASE_URL}/user/cancel-order.php?id=${orderId}`;
}

// ============================================
// NEWSLETTER
// ============================================
function subscribeNewsletter() {
    const emailInput = document.getElementById('newsletterEmail');
    const email = emailInput ? emailInput.value.trim() : '';
    
    if (!email) {
        showNotification('Veuillez entrer votre email', 'error');
        return;
    }
    
    if (!validateEmail(email)) {
        showNotification('Email invalide', 'error');
        return;
    }
    
    fetch(BASE_URL + '/api/newsletter_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=subscribe&email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Merci pour votre inscription !', 'success');
            if (emailInput) emailInput.value = '';
        } else {
            showNotification(data.message || 'Erreur lors de l\'inscription', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ============================================
// CODE PROMO
// ============================================
function applyPromoCode() {
    const promoInput = document.getElementById('promoCodeInput');
    const code = promoInput ? promoInput.value.trim() : '';
    
    if (!code) {
        showNotification('Veuillez entrer un code promo', 'error');
        return;
    }
    
    fetch(BASE_URL + '/api/promo_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=apply&code=${encodeURIComponent(code)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Code promo appliqué avec succès', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Code promo invalide', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'application du code', 'error');
    });
}

function removePromoCode() {
    fetch(BASE_URL + '/api/promo_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=remove'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Code promo retiré', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

// ============================================
// AVIS PRODUITS
// ============================================
function submitReview(form) {
    const productId = form.querySelector('input[name="product_id"]').value;
    const rating = form.querySelector('input[name="rating"]:checked');
    const title = form.querySelector('#reviewTitle');
    const comment = form.querySelector('#reviewComment');
    
    if (!rating) {
        showNotification('Veuillez sélectionner une note', 'error');
        return;
    }
    
    if (!title || !title.value.trim()) {
        showNotification('Veuillez entrer un titre', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'add_review');
    formData.append('product_id', productId);
    formData.append('rating', rating.value);
    formData.append('title', title.value.trim());
    formData.append('comment', comment ? comment.value.trim() : '');
    
    fetch(BASE_URL + '/api/review_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Avis envoyé avec succès', 'success');
            // Réinitialiser le formulaire
            form.reset();
            // Recharger après 2 secondes
            setTimeout(() => location.reload(), 2000);
        } else {
            showNotification(data.message || 'Erreur lors de l\'envoi de l\'avis', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

// ============================================
// NOTIFICATIONS
// ============================================
function showNotification(message, type = 'info') {
    // Supprimer les notifications existantes
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    // Créer la notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">×</button>
    `;
    
    // Ajouter au DOM
    document.body.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Supprimer après 3 secondes
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// ============================================
// ANIMATIONS AU SCROLL
// ============================================
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observer les cartes de produits
    document.querySelectorAll('.product-card').forEach(card => {
        observer.observe(card);
    });
    
    // Observer les sections
    document.querySelectorAll('.admin-section, .about-section, .featured-products').forEach(section => {
        observer.observe(section);
    });
}

// ============================================
// VALIDATION DE FORMULAIRES
// ============================================
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
        
        // Validation email
        if (input.type === 'email' && input.value && !validateEmail(input.value)) {
            input.classList.add('error');
            isValid = false;
        }
    });
    
    if (!isValid) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
    }
    
    return isValid;
}

// ============================================
// PRÉVISUALISATION D'IMAGE
// ============================================
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// ============================================
// CONFIRMATION DE SUPPRESSION
// ============================================
function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    return confirm(message);
}

// ============================================
// FILTRES ET TRI (Page Boutique)
// ============================================
function filterProducts(category) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('category', category);
    window.location.href = currentUrl.toString();
}

function sortProducts(sortBy) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('sort', sortBy);
    window.location.href = currentUrl.toString();
}

// ============================================
// RECHERCHE
// ============================================
function searchProducts() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        const query = searchInput.value.trim();
        if (query.length >= 2) {
            window.location.href = `pages/boutique.php?search=${encodeURIComponent(query)}`;
        }
    }
}

// Recherche en temps réel (optionnel)
function liveSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    searchInput.addEventListener('input', debounce(function() {
        const query = this.value.trim();
        if (query.length >= 2) {
            // Appeler l'API de recherche
            fetch(`/kindwolf/api/search.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    displaySearchResults(data);
                });
        }
    }, 300));
}

// ============================================
// UTILITAIRES
// ============================================

// Debounce pour optimiser les événements
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Formater les prix
function formatPrice(price) {
    return parseFloat(price).toFixed(2).replace('.', ',') + ' €';
}

// Copier dans le presse-papier
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copié dans le presse-papier', 'success');
    }).catch(() => {
        showNotification('Erreur lors de la copie', 'error');
    });
}

// ============================================
// GESTION DES ADRESSES (Page Compte)
// ============================================
function setDefaultAddress(addressId) {
    fetch(BASE_URL + '/api/address_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=set_default&address_id=${addressId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

function deleteAddress(addressId) {
    if (!confirm('Voulez-vous vraiment supprimer cette adresse ?')) {
        return;
    }
    
    fetch(BASE_URL + '/api/address_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=delete&address_id=${addressId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

// ============================================
// NEWSLETTER
// ============================================
function subscribeNewsletter() {
    const emailInput = document.getElementById('newsletterEmail');
    const email = emailInput ? emailInput.value.trim() : '';
    
    if (!email || !validateEmail(email)) {
        showNotification('Veuillez entrer une adresse email valide', 'error');
        return;
    }
    
    fetch(BASE_URL + '/api/newsletter_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=subscribe&email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Inscription réussie à la newsletter', 'success');
            if (emailInput) emailInput.value = '';
        } else {
            showNotification(data.message || 'Erreur lors de l\'inscription', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

// ============================================
// GALERIE D'IMAGES (Page Produit)
// ============================================
function changeMainImage(thumbnailUrl) {
    const mainImage = document.querySelector('.main-image');
    if (mainImage) {
        mainImage.src = thumbnailUrl;
    }
}

// ============================================
// ZOOM IMAGE
// ============================================
function initImageZoom() {
    const images = document.querySelectorAll('.zoomable-image');
    images.forEach(img => {
        img.addEventListener('click', function() {
            const modal = createImageModal(this.src);
            document.body.appendChild(modal);
        });
    });
}

function createImageModal(src) {
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.innerHTML = `
        <div class="modal-backdrop" onclick="this.parentElement.remove()"></div>
        <div class="modal-content">
            <img src="${src}" alt="">
            <button class="modal-close" onclick="this.closest('.image-modal').remove()">×</button>
        </div>
    `;
    return modal;
}

// ============================================
// SMOOTH SCROLL
// ============================================
function smoothScrollTo(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}

// ============================================
// ADMIN: Mise à jour du statut de commande
// ============================================
function updateOrderStatus(orderId, status) {
    if (confirm('Changer le statut de cette commande ?')) {
        fetch('../../api/order_actions.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=update_status&order_id=${orderId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Statut mis à jour', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('Erreur: ' + (data.message || 'Échec de la mise à jour'));
                location.reload();
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur de connexion');
            location.reload();
        });
    } else {
        location.reload();
    }
}

// ============================================
// PRINT (Impression)
// ============================================
function printInvoice() {
    window.print();
}

// ============================================
// EXPORT DE DONNÉES
// ============================================
function exportData(format) {
    window.location.href = `admin/export.php?format=${format}`;
}

console.log('KIND WOLF - Scripts chargés avec succès ✓');