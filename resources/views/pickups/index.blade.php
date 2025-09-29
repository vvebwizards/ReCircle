@extends('layouts.app')

@section('title', 'Pickups')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold mb-6">Pickups</h1>

    @if($pickups->count() === 0)
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
            Aucun pickup pour le moment.
        </div>
    @else
        <div class="overflow-x-auto bg-white shadow rounded">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Address</th>
                        <th class="px-4 py-3 text-left">Window start</th>
                        <th class="px-4 py-3 text-left">Window end</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Tracking</th>
                        <th class="px-4 py-3 text-left">Notes</th>
                        <th class="px-4 py-3 text-left">Actions</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach($pickups as $p)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $p->pickup_address }}</td>
                            <td class="px-4 py-3">{{ optional($p->scheduled_pickup_window_start)->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">{{ optional($p->scheduled_pickup_window_end)->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 capitalize">{{ str_replace('_',' ', $p->status) }}</td>
                            <td class="px-4 py-3 font-mono">{{ $p->tracking_code }}</td>
                            <td class="px-4 py-3">{{ $p->notes }}</td>

                    {{-- ✅ Colonne Actions --}}
                            <td class="flex gap-2">
                                <!-- Bouton Modifier -->
                                <a href="{{ route('pickups.edit', $p->id) }}"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                    Edit
                                </a>

                                <!-- Bouton Supprimer -->
                                <form action="{{ route('pickups.destroy', $p->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this pickup?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                        Delete
                                    </button>
                                </form>
                          </td>
                          
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
{{-- {{ $pickups->links() }} --}}
        </div>
    @endif
</div>
@endsection
