@props(['status'])
<x-badge :color="$status->color()" {{ $attributes }}>{{ $status->label() }}</x-badge>
