<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Menu Item</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/edit-menu-item.css') }}">
</head>
<body>

<div class="page">
    <div class="header">
        <div>
            <h1>Edit Menu Item</h1>
            <p>Update item name, category, price, picture, and description.</p>
        </div>

        <a href="{{ route('admin.menu.index') }}" class="btn btn-secondary">Back to Admin</a>
    </div>

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="edit-grid">
        <div class="card">
            <h2>Current Picture</h2>

            @if($menuItem->image_path)
                <img class="preview-img" src="{{ asset('storage/' . $menuItem->image_path) }}" alt="{{ $menuItem->name }}">
            @else
                <div class="no-image">No image uploaded</div>
            @endif
        </div>

        <div class="card">
            <h2>Edit Details</h2>

            <form method="POST" action="{{ route('admin.menu.items.update', $menuItem) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <label>Category</label>
                <select name="category_id" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $menuItem->category_id == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <label>Item Name</label>
                <input type="text" name="name" value="{{ old('name', $menuItem->name) }}" required>

                <label>Description</label>
                <textarea name="description">{{ old('description', $menuItem->description) }}</textarea>

                <label>Price</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $menuItem->price) }}" required>

                <label>Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $menuItem->sort_order) }}">

                <label>Replace Picture</label>
                <input type="file" name="image" accept="image/png,image/jpeg,image/webp">

                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>