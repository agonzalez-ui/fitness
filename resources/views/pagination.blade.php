@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación" class="flex items-center justify-between gap-3">
        <p class="text-sm text-slate-500">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </p>
        <div class="flex gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex min-h-10 items-center rounded-xl px-4 text-sm font-medium text-slate-300 ring-1 ring-slate-200">Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex min-h-10 items-center rounded-xl bg-white px-4 text-sm font-medium text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">Anterior</a>
            @endif
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex min-h-10 items-center rounded-xl bg-white px-4 text-sm font-medium text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">Siguiente</a>
            @else
                <span class="inline-flex min-h-10 items-center rounded-xl px-4 text-sm font-medium text-slate-300 ring-1 ring-slate-200">Siguiente</span>
            @endif
        </div>
    </nav>
@endif
