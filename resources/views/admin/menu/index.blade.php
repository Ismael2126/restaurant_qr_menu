<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restaurant QR Menu Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/admin-menu.css') }}">
</head>
<body>
<div class="page">

    <div class="header">
    <div>
        <h1>Restaurant QR Menu Admin</h1>
        <p>Add menu items, upload pictures, set prices, and create table QR links.</p>
    </div>

    <div class="header-actions">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-green">Orders</a>
        <a href="{{ route('admin.audit.index') }}" class="btn btn-blue">Audit Logs</a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>
    </div>
</div>

    @if(session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="grid">
        <div class="stack">

            <div class="card">
                <h2>Add Category</h2>

                <form method="POST" action="{{ route('admin.menu.categories.store') }}">
                    @csrf

                    <label>Category Name</label>
                    <input type="text" name="name" placeholder="Food, Drinks, Desserts" required>

                    <label>Sort Order</label>
                    <input type="number" name="sort_order" value="0">

                    <button type="submit">Add Category</button>
                </form>
            </div>

            <div class="card">
                <h2>Add Menu Item</h2>

                <form method="POST" action="{{ route('admin.menu.items.store') }}" enctype="multipart/form-data">
                    @csrf

                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>

                    <label>Item Name</label>
                    <input type="text" name="name" placeholder="Chicken Fried Rice" required>

                    <label>Description</label>
                    <textarea name="description" placeholder="Short description"></textarea>

                    <label>Price</label>
                    <input type="number" step="0.01" name="price" placeholder="45.00" required>

                    <label>Sort Order</label>
                    <input type="number" name="sort_order" value="0">

                    <label>Picture</label>
                    <input type="file" name="image" accept="image/png,image/jpeg,image/webp">

                    <button type="submit">Add Menu Item</button>
                </form>
            </div>

            <div class="card">
                <h2>Create Table QR Link</h2>

                <form method="POST" action="{{ route('admin.menu.tables.store') }}">
                    @csrf

                    <label>Table Name</label>
                    <input type="text" name="table_name" placeholder="Table 1" required>

                    <label>Table Code</label>
                    <input type="text" name="table_code" placeholder="T001" required>

                    <button type="submit">Create Table</button>
                </form>
            </div>

        </div>

        <div class="stack">

            <div class="card">
                <h2 class="section-title">Categories</h2>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($categories as $category)
    <tr>
        <td>{{ $category->name }}</td>
        <td>{{ $category->sort_order }}</td>
        <td>
            @if($category->is_active)
                <span class="badge badge-on">Active</span>
            @else
                <span class="badge badge-off">Hidden</span>
            @endif
        </td>
        <td>
            <div class="actions">
                <form method="POST" action="{{ route('admin.menu.categories.toggle', $category) }}">
                    @csrf
                    <button class="btn-small btn-secondary" type="submit">
                        {{ $category->is_active ? 'Hide' : 'Show' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.menu.categories.delete', $category) }}" onsubmit="return confirm('Delete this category? Only empty categories can be deleted.')">
                    @csrf
                    @method('DELETE')
                    <button class="btn-small btn-danger" type="submit">
                        Delete
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
                            <tr>
                                <td colspan="4">No categories added yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2 class="section-title">Menu Items</h2>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Picture</th>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($menuItems as $item)
                            <tr>
                                <td>
                                    @if($item->image_path)
                                        <img class="menu-img" src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                                    @else
                                        <div class="menu-img"></div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $item->name }}</strong><br>
                                    <small>{{ $item->description }}</small>
                                </td>
                                <td>{{ $item->category?->name }}</td>
                                <td>MVR {{ number_format($item->price, 2) }}</td>
                                <td>
                                    @if($item->is_available)
                                        <span class="badge badge-on">Available</span>
                                    @else
                                        <span class="badge badge-off">Unavailable</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="actions">
                                        <a class="btn btn-small btn-blue" href="{{ route('admin.menu.items.edit', $item) }}">Edit</a>

                                        <form method="POST" action="{{ route('admin.menu.items.toggle', $item) }}">
                                            @csrf
                                            <button class="btn-small btn-secondary" type="submit">
                                                {{ $item->is_available ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.menu.items.delete', $item) }}" onsubmit="return confirm('Delete this item?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-small btn-danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No menu items added yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2 class="section-title">Restaurant Tables</h2>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Table</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Menu Link</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($tables as $table)
                            <tr>
                                <td>{{ $table->table_name }}</td>
                                <td>{{ $table->table_code }}</td>
                                <td>
                                    @if($table->is_active)
                                        <span class="badge badge-on">Active</span>
                                    @else
                                        <span class="badge badge-off">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="qr-link" href="{{ route('public.menu.show', $table->qr_token) }}" target="_blank">
                                        {{ route('public.menu.show', $table->qr_token) }}
                                    </a>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a class="btn btn-small btn-green" href="{{ route('admin.menu.tables.qr', $table) }}" target="_blank">QR</a>

                                        <form method="POST" action="{{ route('admin.menu.tables.toggle', $table) }}">
                                            @csrf
                                            <button class="btn-small btn-secondary" type="submit">
                                                {{ $table->is_active ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No tables created yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="note">
                    Open QR page and print it for each table.
                </p>
            </div>

        </div>
    </div>
</div>
</body>
</html>