@props(['name', 'id' => null, 'bag' => 'default'])
@if ($errors->getBag($bag)->has($name))
    <p @if ($id) id="{{ $id }}" @endif class="mt-1.5 text-sm font-medium text-red-700">{{ $errors->getBag($bag)->first($name) }}</p>
@endif
