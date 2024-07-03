
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

    <label for="{{ $name }}" class="flex items-center">
        {{-- <label class="flex items-center"> --}}
            <input type="checkbox" name="{{ $name }}" @required($is_required)  id="{{ $name }}"
             class="form-checkbox h-5 w-5 text-indigo-500 dark:bg-gray-700 border border-gray-300 dark:border-gray-700 rounded focus:outline-none ltr:mr-3 rtl:ml-3"
             @if ($is_update)
                @checked($datum->{$name})
              @else
                @checked(old($name))
             @endif
             >
            <span>
                {{ $label ?? $title }} @if ($is_required) <span class="text-[red]">*</span>@endif
            </span>
          {{-- </label> --}}

    </label>
</div>
