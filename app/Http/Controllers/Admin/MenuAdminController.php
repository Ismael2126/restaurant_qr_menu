<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AuditHelper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuAdminController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();

        $menuItems = MenuItem::with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $tables = RestaurantTable::orderBy('table_code')->get();

        return view('admin.menu.index', compact('categories', 'menuItems', 'tables'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        AuditHelper::log('Create', 'Category', 'Created category: ' . $category->name);

        return back()->with('success', 'Category added successfully.');
    }

    public function toggleCategory(Category $category)
    {
        $category->update([
            'is_active' => !$category->is_active,
        ]);

        AuditHelper::log(
            'Toggle',
            'Category',
            'Changed category status: ' . $category->name . ' to ' . ($category->is_active ? 'Active' : 'Hidden')
        );

        return back()->with('success', 'Category status updated.');
    }

    public function deleteCategory(Category $category)
    {
        if ($category->menuItems()->count() > 0) {
            return back()->withErrors([
                'category' => 'Cannot delete this category because it has menu items. Move or delete the items first.',
            ]);
        }

        $categoryName = $category->name;

        $category->delete();

        AuditHelper::log('Delete', 'Category', 'Deleted category: ' . $categoryName);

        return back()->with('success', 'Category deleted successfully.');
    }

    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('menu-items', 'public');
        }

        $menuItem = MenuItem::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'image_path' => $imagePath,
            'is_available' => true,
        ]);

        AuditHelper::log(
            'Create',
            'Menu Item',
            'Created menu item: ' . $menuItem->name . ' / Price: MVR ' . number_format($menuItem->price, 2)
        );

        return back()->with('success', 'Menu item added successfully.');
    }

    public function editItem(MenuItem $menuItem)
    {
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.menu.edit-item', compact('menuItem', 'categories'));
    }

    public function updateItem(Request $request, MenuItem $menuItem)
    {
        $oldName = $menuItem->name;
        $oldPrice = $menuItem->price;

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = $menuItem->image_path;
        $pictureChanged = false;

        if ($request->hasFile('image')) {
            if ($menuItem->image_path && Storage::disk('public')->exists($menuItem->image_path)) {
                Storage::disk('public')->delete($menuItem->image_path);
            }

            $imagePath = $request->file('image')->store('menu-items', 'public');
            $pictureChanged = true;
        }

        $menuItem->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'image_path' => $imagePath,
        ]);

        AuditHelper::log(
            'Update',
            'Menu Item',
            'Updated menu item: ' . $oldName .
            ' to ' . $menuItem->name .
            ' / Price: MVR ' . number_format($oldPrice, 2) .
            ' to MVR ' . number_format($menuItem->price, 2) .
            ($pictureChanged ? ' / Picture changed' : '')
        );

        return redirect()
            ->route('admin.menu.index')
            ->with('success', 'Menu item updated successfully.');
    }

    public function toggleItem(MenuItem $menuItem)
    {
        $menuItem->update([
            'is_available' => !$menuItem->is_available,
        ]);

        AuditHelper::log(
            'Toggle',
            'Menu Item',
            'Changed item status: ' . $menuItem->name . ' to ' . ($menuItem->is_available ? 'Available' : 'Unavailable')
        );

        return back()->with('success', 'Menu item status updated.');
    }

    public function deleteItem(MenuItem $menuItem)
    {
        $itemName = $menuItem->name;

        if ($menuItem->image_path && Storage::disk('public')->exists($menuItem->image_path)) {
            Storage::disk('public')->delete($menuItem->image_path);
        }

        $menuItem->delete();

        AuditHelper::log('Delete', 'Menu Item', 'Deleted menu item: ' . $itemName);

        return back()->with('success', 'Menu item deleted successfully.');
    }

    public function storeTable(Request $request)
    {
        $validated = $request->validate([
            'table_name' => 'required|string|max:100',
            'table_code' => 'required|string|max:50|unique:restaurant_tables,table_code',
        ]);

        $table = RestaurantTable::create([
            'table_name' => $validated['table_name'],
            'table_code' => strtoupper($validated['table_code']),
            'qr_token' => Str::random(32),
            'is_active' => true,
        ]);

        AuditHelper::log(
            'Create',
            'Table',
            'Created table: ' . $table->table_name . ' / Code: ' . $table->table_code
        );

        return back()->with('success', 'Table created successfully.');
    }

    public function toggleTable(RestaurantTable $restaurantTable)
    {
        $restaurantTable->update([
            'is_active' => !$restaurantTable->is_active,
        ]);

        AuditHelper::log(
            'Toggle',
            'Table',
            'Changed table status: ' . $restaurantTable->table_name . ' to ' . ($restaurantTable->is_active ? 'Active' : 'Inactive')
        );

        return back()->with('success', 'Table status updated.');
    }

    public function qrPage(RestaurantTable $restaurantTable)
    {
        $menuUrl = route('public.menu.show', $restaurantTable->qr_token);

        return view('admin.menu.qr', compact('restaurantTable', 'menuUrl'));
    }
}