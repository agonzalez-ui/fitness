<x-layouts.admin :title="'Editar '.$client->name">
    <x-page-header :title="'Editar '.$client->first_name" :back="route('admin.clients.show', $client)" />

    <form method="POST" action="{{ route('admin.clients.update', $client) }}" class="mx-auto max-w-3xl space-y-6">
        @csrf @method('PUT')
        <x-card>
            @include('admin.clients._profile-fields', ['client' => $client])
        </x-card>
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-button :href="route('admin.clients.show', $client)" variant="ghost">Cancelar</x-button>
            <x-button size="lg" icon="check">Guardar cambios</x-button>
        </div>
    </form>
</x-layouts.admin>
