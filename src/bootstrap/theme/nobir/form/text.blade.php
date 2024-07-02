@props([
    'name',
    'label' => null,
    'is_required' => true,
    'is_update' => false,

])

@php
    $title = str()->headline(str_replace('_id', '', $name));

@endphp

<div class="flex-shrink max-w-full px-4 w-full md:w-1/2 mb-6">

    <label for="{{ $name }}" class="inline-block">{{$label ?? $title }}
        @if ($is_required)
            <span class="text-[red]">*</span>
        @endif
    </label>

    <input type="text" name="{{ $name }}" @required($is_required)
        @if ($is_update) value="{{ $data->{$name} }}"
         @else
            value="{{ old($name) }}" @endif
        class="w-full leading-5 relative py-2 px-4 rounded text-gray-800 bg-white border border-gray-300 overflow-x-auto focus:outline-none focus:border-gray-400 focus:ring-0 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-700 dark:focus:border-gray-600"
        id="{{ $name }}" placeholder="Please, enter {{ str()->lower($title) }}">
    @error('{{ $name }}')
        <span class="text-[red]">{{ $message }}</span>
    @enderror
</div>
