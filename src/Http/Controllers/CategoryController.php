<?php

namespace Rapidez\Core\Http\Controllers;

use Rapidez\Core\Models\Category;

class CategoryController
{
    public function show(int $categoryId)
    {
        $category = Category::findOrFail($categoryId);

        config(['frontend.category' => $category->only('entity_id')]);

        return view('rapidez::category.overview', compact('category'));
    }
}