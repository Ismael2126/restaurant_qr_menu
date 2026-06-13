<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restaurant Menu - {{ $table->table_name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('css/public-menu.css') }}">
    <script src="{{ asset('js/public-menu.js') }}" defer></script>
</head>
<body data-order-url="{{ route('public.menu.order', $table->qr_token) }}">

<div class="mobile-page">

    <header class="menu-header">
        <div class="table-pill">{{ $table->table_name }}</div>

        <div class="header-row">
            <div>
                <h1>Menu</h1>
                <p>Select items and send your order from this table.</p>
            </div>

            <button type="button" class="header-cart-btn js-open-cart">
                Cart
                <span id="cartCountTop">0</span>
            </button>
        </div>
    </header>

    <nav class="category-nav">
        @foreach($categories as $category)
            @if($category->menuItems->count() > 0)
                <a href="#category-{{ $category->id }}">{{ $category->name }}</a>
            @endif
        @endforeach
    </nav>

    <main class="menu-content">
        @php
            $hasItems = false;
        @endphp

        @foreach($categories as $category)
            @if($category->menuItems->count() > 0)
                @php
                    $hasItems = true;
                @endphp

                <section class="menu-section" id="category-{{ $category->id }}">
                    <h2>{{ $category->name }}</h2>

                    <div class="menu-list">
                        @foreach($category->menuItems as $item)
                            <article class="food-card">
                                <div class="food-image-wrap">
                                    @if($item->image_path)
                                        <img class="food-image" src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                                    @else
                                        <div class="food-image no-image">No Image</div>
                                    @endif
                                </div>

                                <div class="food-info">
                                    <div class="food-top">
                                        <h3>{{ $item->name }}</h3>
                                        <div class="food-price">MVR {{ number_format($item->price, 2) }}</div>
                                    </div>

                                    @if($item->description)
                                        <p>{{ $item->description }}</p>
                                    @else
                                        <p class="muted">Freshly prepared item.</p>
                                    @endif

                                    <button
                                        type="button"
                                        class="add-btn js-add-to-cart"
                                        data-id="{{ $item->id }}"
                                        data-name="{{ $item->name }}"
                                        data-price="{{ $item->price }}"
                                    >
                                        Add to Order
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

        @if(!$hasItems)
            <div class="empty-state">
                No menu items available right now.
            </div>
        @endif
    </main>

</div>

<div class="bottom-cart-bar" id="bottomCartBar">
    <div>
        <strong id="bottomCartCount">0 items</strong>
        <span id="bottomCartTotal">MVR 0.00</span>
    </div>

    <button type="button" class="js-open-cart">View Order</button>
</div>

<div class="cart-backdrop" id="cartBackdrop"></div>

<aside class="cart-panel" id="cartPanel">
    <div class="cart-header">
        <div>
            <h2>Your Order</h2>
            <p>{{ $table->table_name }}</p>
        </div>

        <button type="button" class="close-btn js-close-cart">×</button>
    </div>

    <div class="cart-items" id="cartItems">
        <div class="empty-cart">Your cart is empty.</div>
    </div>

    <div class="cart-note">
        <label for="customerNote">Note to kitchen</label>
        <textarea id="customerNote" placeholder="Example: less spicy, no onion, extra sauce..."></textarea>
    </div>

    <div class="cart-footer">
        <div class="cart-total-row">
            <span>Total</span>
            <strong id="cartTotal">MVR 0.00</strong>
        </div>

        <button type="button" class="submit-order-btn" id="submitOrderBtn">
            Send Order
        </button>

        <div class="order-message" id="orderMessage"></div>
    </div>
</aside>

<div class="success-modal" id="successModal">
    <div class="success-card">
        <div class="success-icon">✓</div>
        <h2>Order Sent</h2>
        <p>Your order has been sent to the restaurant.</p>
        <strong id="successOrderNumber"></strong>

        <button type="button" id="successCloseBtn">Continue</button>
    </div>
</div>

</body>
</html>