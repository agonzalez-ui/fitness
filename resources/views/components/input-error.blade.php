@props(['name', 'id' => null])
@error($name)
    <p @if ($id) id="{{ $id }}" @endif class="mt-1.5 text-sm font-medium text-red-700">{{ $message }}</p>
@enderror
