
@props([
    'method' => 'POST',
    'is_update' => false, //you have to pass id of the model
    'route',
])


<form method="{{ $method }}"
    @if ($is_update) action="{{ route($route, $is_update) }}" @else action="{{ route($route) }}" @endif
    class="flex flex-wrap flex-row -mx-4">

    @csrf

    {{ $slot }}
    
 {{-- save and save new  ( Submit button )  --}}
    <div class="flex-shrink max-w-full px-4 w-full text-center">
        <button name='redirect' value='back' type="submit"
            class="py-2 px-5 inline-block text-center rounded mb-3 leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-gray-100 hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0">
            Save
        </button>
        <button name='redirect' value='new' type="submit"
            class="py-2 px-5 inline-block text-center rounded mb-3 leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-gray-100 hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0">
            Save & New
        </button>
    </div>
</form>
