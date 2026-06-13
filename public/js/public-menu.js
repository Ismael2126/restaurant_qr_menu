/* =========================================================
   RESTAURANT QR MENU - PUBLIC CUSTOMER MENU JS
   File: public/js/public-menu.js
   Page: resources/views/public/menu.blade.php
   ========================================================= */

let cart = {};
let isSubmitting = false;

document.addEventListener('DOMContentLoaded', function () {
    bindStaticEvents();
    renderCart();
});

function bindStaticEvents() {
    document.querySelectorAll('.js-open-cart').forEach(function (button) {
        button.addEventListener('click', openCart);
    });

    document.querySelectorAll('.js-close-cart').forEach(function (button) {
        button.addEventListener('click', closeCart);
    });

    document.getElementById('cartBackdrop').addEventListener('click', closeCart);

    document.querySelectorAll('.js-add-to-cart').forEach(function (button) {
        button.addEventListener('click', function () {
            addToCart(
                button.dataset.id,
                button.dataset.name,
                parseFloat(button.dataset.price)
            );
        });
    });

    document.getElementById('cartItems').addEventListener('click', function (event) {
        const button = event.target.closest('button');

        if (!button) {
            return;
        }

        const action = button.dataset.action;
        const id = button.dataset.id;

        if (action === 'increase') {
            increaseQty(id);
        }

        if (action === 'decrease') {
            decreaseQty(id);
        }

        if (action === 'remove') {
            removeItem(id);
        }
    });

    document.getElementById('submitOrderBtn').addEventListener('click', submitOrder);

    document.getElementById('successCloseBtn').addEventListener('click', function () {
        document.getElementById('successModal').classList.remove('show');
    });
}

function openCart() {
    document.getElementById('cartBackdrop').classList.add('show');
    document.getElementById('cartPanel').classList.add('show');
}

function closeCart() {
    document.getElementById('cartBackdrop').classList.remove('show');
    document.getElementById('cartPanel').classList.remove('show');
}

function addToCart(id, name, price) {
    if (!cart[id]) {
        cart[id] = {
            id: parseInt(id),
            name: name,
            price: price,
            quantity: 1
        };
    } else {
        cart[id].quantity += 1;
    }

    renderCart();
}

function increaseQty(id) {
    if (!cart[id]) {
        return;
    }

    cart[id].quantity += 1;
    renderCart();
}

function decreaseQty(id) {
    if (!cart[id]) {
        return;
    }

    if (cart[id].quantity > 1) {
        cart[id].quantity -= 1;
    } else {
        delete cart[id];
    }

    renderCart();
}

function removeItem(id) {
    delete cart[id];
    renderCart();
}

function renderCart() {
    const cartItems = document.getElementById('cartItems');
    const cartCountTop = document.getElementById('cartCountTop');
    const bottomCartCount = document.getElementById('bottomCartCount');
    const bottomCartTotal = document.getElementById('bottomCartTotal');
    const bottomCartBar = document.getElementById('bottomCartBar');
    const cartTotal = document.getElementById('cartTotal');
    const submitOrderBtn = document.getElementById('submitOrderBtn');

    const items = Object.values(cart);

    let totalQty = 0;
    let totalAmount = 0;

    if (items.length === 0) {
        cartItems.innerHTML = '<div class="empty-cart">Your cart is empty.</div>';

        cartCountTop.textContent = '0';
        bottomCartCount.textContent = '0 items';
        bottomCartTotal.textContent = 'MVR 0.00';
        cartTotal.textContent = 'MVR 0.00';

        bottomCartBar.classList.remove('show');
        submitOrderBtn.disabled = true;

        return;
    }

    let html = '';

    items.forEach(function (item) {
        const lineTotal = item.price * item.quantity;

        totalQty += item.quantity;
        totalAmount += lineTotal;

        html += `
            <div class="cart-row">
                <div class="cart-row-top">
                    <div class="cart-row-name">${escapeHtml(item.name)}</div>
                    <div class="cart-row-price">MVR ${lineTotal.toFixed(2)}</div>
                </div>

                <div class="qty-controls">
                    <div class="qty-buttons">
                        <button type="button" class="qty-btn" data-action="decrease" data-id="${item.id}">−</button>
                        <span class="qty-number">${item.quantity}</span>
                        <button type="button" class="qty-btn" data-action="increase" data-id="${item.id}">+</button>
                    </div>

                    <button type="button" class="remove-btn" data-action="remove" data-id="${item.id}">
                        Remove
                    </button>
                </div>
            </div>
        `;
    });

    cartItems.innerHTML = html;

    cartCountTop.textContent = totalQty;
    bottomCartCount.textContent = totalQty + (totalQty === 1 ? ' item' : ' items');
    bottomCartTotal.textContent = 'MVR ' + totalAmount.toFixed(2);
    cartTotal.textContent = 'MVR ' + totalAmount.toFixed(2);

    bottomCartBar.classList.add('show');
    submitOrderBtn.disabled = false;
}

function submitOrder() {
    if (isSubmitting) {
        return;
    }

    const items = Object.values(cart).map(function (item) {
        return {
            id: item.id,
            quantity: item.quantity
        };
    });

    if (items.length === 0) {
        return;
    }

    const submitOrderBtn = document.getElementById('submitOrderBtn');
    const orderMessage = document.getElementById('orderMessage');
    const customerNote = document.getElementById('customerNote').value;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const orderUrl = document.body.dataset.orderUrl;

    isSubmitting = true;
    submitOrderBtn.disabled = true;
    submitOrderBtn.textContent = 'Sending...';
    orderMessage.textContent = '';

    fetch(orderUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            items: items,
            customer_note: customerNote
        })
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Order failed');
            }

            return response.json();
        })
        .then(function (data) {
            cart = {};
            renderCart();

            document.getElementById('customerNote').value = '';
            document.getElementById('successOrderNumber').textContent = data.order_number;

            closeCart();
            document.getElementById('successModal').classList.add('show');

            submitOrderBtn.textContent = 'Send Order';
            isSubmitting = false;
        })
        .catch(function () {
            orderMessage.textContent = 'Could not send order. Please call waiter.';
            submitOrderBtn.disabled = false;
            submitOrderBtn.textContent = 'Send Order';
            isSubmitting = false;
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}