/* =========================================================
   RESTAURANT QR MENU - ADMIN ORDERS DASHBOARD JS
   File: public/js/admin-orders.js
   Page: resources/views/admin/orders/index.blade.php
   ========================================================= */

const POLL_INTERVAL_MS = 8000;
const ELAPSED_UPDATE_MS = 30000;
const STATUS_KEYS = ['new', 'preparing', 'ready', 'completed', 'cancelled'];

let knownOrderIds = new Set();
let pollTimer = null;
let audioContext = null;
let csrfToken = '';
let dataUrl = '';

document.addEventListener('DOMContentLoaded', function () {
    dataUrl = document.body.dataset.dataUrl;
    csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    seedKnownOrders();
    initFilters();
    initToggles();
    initStatusForms(document.getElementById('ordersGrid'));
    initNextStatusButtons(document.getElementById('ordersGrid'));

    updateElapsedTimes();
    updateCounts();

    document.addEventListener('click', function unlockAudio() {
        primeAudio();
    }, { once: true });

    document.getElementById('refreshNow').addEventListener('click', function () {
        fetchOrders();
    });

    setInterval(updateElapsedTimes, ELAPSED_UPDATE_MS);

    startPolling();
});

/* =========================
   SEEDING
   ========================= */

function seedKnownOrders() {
    knownOrderIds = new Set();

    document.querySelectorAll('#ordersGrid .order-card').forEach(function (card) {
        knownOrderIds.add(parseInt(card.dataset.orderId, 10));
    });
}

/* =========================
   POLLING
   ========================= */

function startPolling() {
    stopPolling();

    if (!isAutoRefreshOn()) {
        return;
    }

    pollTimer = setInterval(fetchOrders, POLL_INTERVAL_MS);
}

function stopPolling() {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
}

function fetchOrders() {
    fetch(dataUrl, {
        headers: { 'Accept': 'application/json' }
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Failed to load orders');
            }

            return response.json();
        })
        .then(function (data) {
            setLiveStatus(true);
            renderOrders(data.orders);
        })
        .catch(function () {
            setLiveStatus(false);
        });
}

function setLiveStatus(online) {
    const liveStatus = document.getElementById('liveStatus');
    const liveStatusText = document.getElementById('liveStatusText');

    if (online) {
        liveStatus.classList.remove('is-offline');
        liveStatusText.textContent = 'Live';
    } else {
        liveStatus.classList.add('is-offline');
        liveStatusText.textContent = 'Connection lost';
    }
}

/* =========================
   RENDERING
   ========================= */

function renderOrders(orders) {
    const grid = document.getElementById('ordersGrid');
    const emptyState = document.getElementById('emptyState');

    if (!orders.length) {
        grid.innerHTML = '';
        emptyState.style.display = '';
        knownOrderIds = new Set();
        updateCounts();

        return;
    }

    emptyState.style.display = 'none';

    const seenIds = new Set();
    let previousElement = null;

    orders.forEach(function (order) {
        seenIds.add(order.id);

        let card = grid.querySelector('.order-card[data-order-id="' + order.id + '"]');

        if (!card) {
            const isNewOrder = !knownOrderIds.has(order.id);
            card = buildCard(order);

            if (isNewOrder) {
                card.classList.add('order-card-new');
                notifyNewOrder(order);

                setTimeout(function () {
                    card.classList.remove('order-card-new');
                }, 3000);
            }
        } else {
            updateCard(card, order);
        }

        if (previousElement) {
            if (previousElement.nextElementSibling !== card) {
                previousElement.insertAdjacentElement('afterend', card);
            }
        } else if (grid.firstElementChild !== card) {
            grid.insertBefore(card, grid.firstElementChild);
        }

        previousElement = card;
    });

    Array.from(grid.children).forEach(function (child) {
        const id = parseInt(child.dataset.orderId, 10);

        if (!seenIds.has(id)) {
            child.remove();
        }
    });

    knownOrderIds = seenIds;

    updateElapsedTimes();
    updateCounts();
    applyFilter();
}

function buildCard(order) {
    const card = document.createElement('div');
    card.className = 'order-card status-' + order.status;
    card.dataset.orderId = order.id;
    card.dataset.status = order.status;
    card.dataset.createdAt = order.created_at;

    card.innerHTML = cardInnerHtml(order);

    initStatusForms(card);
    initNextStatusButtons(card);

    return card;
}

function cardInnerHtml(order) {
    let itemsHtml = '';

    order.items.forEach(function (item) {
        itemsHtml += '' +
            '<div class="item-row">' +
                '<div class="item-main">' +
                    '<span class="item-qty">' + item.quantity + '&times;</span>' +
                    '<span class="item-text">' +
                        '<span class="item-name">' + escapeHtml(item.name) + '</span>' +
                        '<span class="item-unit">MVR ' + item.unit_price + ' each</span>' +
                    '</span>' +
                '</div>' +
                '<b class="item-total">MVR ' + item.line_total + '</b>' +
            '</div>';
    });

    const noteDisplay = order.customer_note ? '' : ' style="display:none;"';
    const noteText = order.customer_note ? escapeHtml(order.customer_note) : '';

    const nextBtnDisplay = order.next_status ? '' : ' style="display:none;"';

    return '' +
        '<div class="order-top">' +
            '<div>' +
                '<h2>' + escapeHtml(order.table_name) + '</h2>' +
                '<p>' + escapeHtml(order.order_number) + '</p>' +
            '</div>' +
            '<span class="status-badge badge-' + order.status + '" data-status-badge>' + escapeHtml(order.status_label.toUpperCase()) + '</span>' +
        '</div>' +

        '<div class="order-meta-row">' +
            '<span class="order-time">' + escapeHtml(order.created_at_label) + '</span>' +
            '<span class="order-elapsed" data-elapsed data-created-at="' + order.created_at + '">just now</span>' +
        '</div>' +

        '<div class="items-list">' + itemsHtml + '</div>' +

        '<div class="note-box" data-note-box' + noteDisplay + '>' +
            '<strong>Note:</strong> <span data-note-text>' + noteText + '</span>' +
        '</div>' +

        '<div class="total-row">' +
            '<span>Total</span>' +
            '<strong>MVR ' + order.total_amount + '</strong>' +
        '</div>' +

        '<div class="card-actions">' +
            '<button type="button" class="btn-next-status" data-next-status-btn data-order-id="' + order.id + '" data-next-status="' + (order.next_status || '') + '"' + nextBtnDisplay + '>' +
                escapeHtml(order.next_status_label || '') +
            '</button>' +

            '<div class="status-form-row">' +
                '<form method="POST" action="' + order.status_url + '" class="status-form" data-status-form data-order-id="' + order.id + '">' +
                    '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                    '<select name="status" data-status-select>' +
                        STATUS_KEYS.map(function (status) {
                            return '<option value="' + status + '"' + (order.status === status ? ' selected' : '') + '>' +
                                escapeHtml(status.charAt(0).toUpperCase() + status.slice(1)) +
                                '</option>';
                        }).join('') +
                    '</select>' +
                    '<button type="submit" class="btn-update">Update</button>' +
                '</form>' +
                '<a href="' + order.ticket_url + '" target="_blank" class="btn btn-ticket" data-ticket-link>Print Ticket</a>' +
            '</div>' +
        '</div>';
}

function updateCard(card, order) {
    const statusChanged = card.dataset.status !== order.status;

    card.dataset.status = order.status;
    card.className = 'order-card status-' + order.status;

    const badge = card.querySelector('[data-status-badge]');

    if (badge) {
        badge.className = 'status-badge badge-' + order.status;
        badge.textContent = order.status_label.toUpperCase();
    }

    const select = card.querySelector('[data-status-select]');

    if (select && document.activeElement !== select) {
        select.value = order.status;
    }

    const nextBtn = card.querySelector('[data-next-status-btn]');

    if (nextBtn) {
        if (order.next_status) {
            nextBtn.style.display = '';
            nextBtn.dataset.nextStatus = order.next_status;
            nextBtn.textContent = order.next_status_label;
        } else {
            nextBtn.style.display = 'none';
        }
    }

    if (statusChanged) {
        applyFilter();
    }
}

/* =========================
   STATUS UPDATES (AJAX)
   ========================= */

function initStatusForms(container) {
    container.querySelectorAll('[data-status-form]').forEach(function (form) {
        if (form.dataset.bound === 'true') {
            return;
        }

        form.dataset.bound = 'true';

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const select = form.querySelector('[data-status-select]');
            submitStatus(form.dataset.orderId, select.value, form.querySelector('.btn-update'));
        });
    });
}

function initNextStatusButtons(container) {
    container.querySelectorAll('[data-next-status-btn]').forEach(function (button) {
        if (button.dataset.bound === 'true') {
            return;
        }

        button.dataset.bound = 'true';

        button.addEventListener('click', function () {
            const nextStatus = button.dataset.nextStatus;

            if (!nextStatus) {
                return;
            }

            submitStatus(button.dataset.orderId, nextStatus, button);
        });
    });
}

function submitStatus(orderId, status, triggerElement) {
    const card = document.querySelector('.order-card[data-order-id="' + orderId + '"]');
    const form = card ? card.querySelector('[data-status-form]') : null;

    if (!form) {
        return;
    }

    if (triggerElement) {
        triggerElement.disabled = true;
    }

    fetch(form.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Failed to update status');
            }

            return response.json();
        })
        .then(function (data) {
            if (data.order) {
                knownOrderIds.add(data.order.id);
                updateCard(card, data.order);
                updateCounts();
                applyFilter();
            }
        })
        .catch(function () {
            window.alert('Could not update order status. Please try again.');
        })
        .finally(function () {
            if (triggerElement) {
                triggerElement.disabled = false;
            }
        });
}

/* =========================
   FILTERS & COUNTS
   ========================= */

function initFilters() {
    document.querySelectorAll('.filter-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(function (t) {
                t.classList.remove('is-active');
            });

            tab.classList.add('is-active');
            localStorage.setItem('adminOrdersFilter', tab.dataset.filter);
            applyFilter();
        });
    });

    const savedFilter = localStorage.getItem('adminOrdersFilter');
    const savedTab = savedFilter ? document.querySelector('.filter-tab[data-filter="' + savedFilter + '"]') : null;

    if (savedTab) {
        document.querySelectorAll('.filter-tab').forEach(function (t) {
            t.classList.remove('is-active');
        });

        savedTab.classList.add('is-active');
    }

    applyFilter();
}

function getActiveFilter() {
    const activeTab = document.querySelector('.filter-tab.is-active');
    return activeTab ? activeTab.dataset.filter : 'all';
}

function applyFilter() {
    const filter = getActiveFilter();

    document.querySelectorAll('#ordersGrid .order-card').forEach(function (card) {
        const matches = filter === 'all' || card.dataset.status === filter;
        card.classList.toggle('is-hidden', !matches);
    });
}

function updateCounts() {
    const counts = { all: 0, new: 0, preparing: 0, ready: 0, completed: 0, cancelled: 0 };

    document.querySelectorAll('#ordersGrid .order-card').forEach(function (card) {
        counts.all += 1;

        if (counts[card.dataset.status] !== undefined) {
            counts[card.dataset.status] += 1;
        }
    });

    Object.keys(counts).forEach(function (key) {
        const el = document.querySelector('.count[data-count="' + key + '"]');

        if (el) {
            el.textContent = counts[key];
        }
    });
}

/* =========================
   TOGGLES (SOUND / AUTO-REFRESH)
   ========================= */

function initToggles() {
    const soundToggle = document.getElementById('soundToggle');
    const refreshToggle = document.getElementById('refreshToggle');

    setToggleState(soundToggle, isSoundOn(), 'Sound');
    setToggleState(refreshToggle, isAutoRefreshOn(), 'Auto-refresh');

    soundToggle.addEventListener('click', function () {
        const nowOn = !isSoundOn();
        localStorage.setItem('adminOrdersSound', nowOn ? 'on' : 'off');
        setToggleState(soundToggle, nowOn, 'Sound');

        if (nowOn) {
            primeAudio();
        }
    });

    refreshToggle.addEventListener('click', function () {
        const nowOn = !isAutoRefreshOn();
        localStorage.setItem('adminOrdersAutoRefresh', nowOn ? 'on' : 'off');
        setToggleState(refreshToggle, nowOn, 'Auto-refresh');

        if (nowOn) {
            fetchOrders();
            startPolling();
        } else {
            stopPolling();
        }
    });
}

function setToggleState(button, isOn, label) {
    button.classList.toggle('is-on', isOn);
    button.textContent = label + ': ' + (isOn ? 'On' : 'Off');
}

function isSoundOn() {
    return localStorage.getItem('adminOrdersSound') !== 'off';
}

function isAutoRefreshOn() {
    return localStorage.getItem('adminOrdersAutoRefresh') !== 'off';
}

/* =========================
   SOUND ALERT
   ========================= */

function primeAudio() {
    if (!audioContext) {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;

        if (AudioCtx) {
            audioContext = new AudioCtx();
        }
    }

    if (audioContext && audioContext.state === 'suspended') {
        audioContext.resume();
    }
}

function playNewOrderSound() {
    if (!isSoundOn()) {
        return;
    }

    primeAudio();

    if (!audioContext) {
        return;
    }

    const now = audioContext.currentTime;

    [880, 1175].forEach(function (frequency, index) {
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.type = 'sine';
        oscillator.frequency.value = frequency;

        const start = now + index * 0.16;
        const end = start + 0.28;

        gainNode.gain.setValueAtTime(0.0001, start);
        gainNode.gain.exponentialRampToValueAtTime(0.3, start + 0.02);
        gainNode.gain.exponentialRampToValueAtTime(0.0001, end);

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.start(start);
        oscillator.stop(end);
    });
}

/* =========================
   TOASTS
   ========================= */

function notifyNewOrder(order) {
    playNewOrderSound();
    showToast(order);
}

function showToast(order) {
    const container = document.getElementById('toastContainer');

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = '<strong>New Order</strong>' + escapeHtml(order.table_name) + ' &mdash; ' + escapeHtml(order.order_number);

    container.appendChild(toast);

    setTimeout(function () {
        toast.remove();
    }, 6000);
}

/* =========================
   ELAPSED TIME
   ========================= */

function updateElapsedTimes() {
    document.querySelectorAll('[data-elapsed]').forEach(function (el) {
        const createdAt = new Date(el.dataset.createdAt);
        const diffMs = Date.now() - createdAt.getTime();
        const diffMin = Math.floor(diffMs / 60000);

        let text;

        if (diffMin <= 0) {
            text = 'Just now';
        } else if (diffMin === 1) {
            text = '1 min ago';
        } else if (diffMin < 60) {
            text = diffMin + ' min ago';
        } else {
            const hours = Math.floor(diffMin / 60);
            const mins = diffMin % 60;
            text = hours + 'h ' + mins + 'm ago';
        }

        el.textContent = text;
        el.classList.remove('elapsed-warn', 'elapsed-danger');

        const card = el.closest('.order-card');
        const status = card ? card.dataset.status : null;

        if (status === 'new' || status === 'preparing') {
            if (diffMin >= 15) {
                el.classList.add('elapsed-danger');
            } else if (diffMin >= 7) {
                el.classList.add('elapsed-warn');
            }
        }
    });
}

/* =========================
   HELPERS
   ========================= */

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
