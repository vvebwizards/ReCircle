@extends('layouts.app')

@section('content')
<main class="container py-10">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold flex items-center gap-2">
      <i class="fa-solid fa-truck"></i> Pickups Management
    </h1>
    <a href="{{ route('pickups.create') }}" class="btn primary sm">
      <i class="fa-solid fa-plus"></i> New Pickup
    </a>
  </div>

  @if($pickups->isEmpty())
    <div class="bg-white border rounded-xl p-6 text-gray-600">
      Aucun pickup pour le moment.
    </div>
  @else
    <div class="overflow-x-auto bg-white border rounded-xl">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-100 text-gray-700">
          <tr>
            <th class="text-left p-3">Title of Waste Item </th>
            <th class="text-left p-3">Window Start</th>
            <th class="text-left p-3">Window End</th>
            <th class="text-left p-3">Status</th>
            <th class="text-left p-3">Notes</th>
            <th class="text-left p-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($pickups as $p)
            <tr class="border-t">
              <td class="p-3">{{ $p->wasteItem->title ?? '—' }}</td>
              <td class="p-3">{{ \Illuminate\Support\Carbon::parse($p->window_start)->format('Y-m-d H:i') }}</td>
              <td class="p-3">{{ \Illuminate\Support\Carbon::parse($p->window_end)->format('Y-m-d H:i') }}</td>
              <td class="p-3">
                <span class="px-2 py-1 rounded-full text-xs font-semibold
                  @class([
                    'bg-emerald-100 text-emerald-700' => $p->status === 'picked',
                    'bg-amber-100 text-amber-700'     => $p->status === 'scheduled',
                    'bg-slate-100 text-slate-700'     => !in_array($p->status, ['picked','scheduled']),
                  ])
                ">{{ ucfirst($p->status) }}</span>
              </td>
              <td class="p-3">{{ Str::limit($p->notes, 50) }}</td>
              <td class="p-3">
                    @if(optional(auth()->user()->role)->value === 'courier')
                        <a href="{{ route('deliveries.createFromPickup', $p) }}"
                        class="text-blue-600 hover:underline">Select delivery</a>
                    @else
                        <a href="{{ route('pickups.show', $p) }}" class="text-blue-600 hover:underline">View</a>
                    @endif
                </td>

            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $pickups->links() }}
    </div>
  @endif
</main>
@endsection
