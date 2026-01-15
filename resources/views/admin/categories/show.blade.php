@extends('layouts.admin')

@section('page-title', 'Category Details')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ $category->name }}
                        </h3>

                        @if(!empty($category->description))
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ $category->description }}
                            </p>
                        @endif
                    </div>

                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-2">
                            Products in this category:
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $category->products->count() }} products
                        </p>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('admin.categories.index') }}"
                           class="mr-4 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                            Back to Categories
                        </a>
                        <a href="{{ route('admin.categories.edit', $category) }}"
                           class="mr-4 text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-100">
                            Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
