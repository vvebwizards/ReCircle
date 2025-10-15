@extends('layouts.app')

@section('title', 'My Deliveries')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <h1 class="text-2xl font-semibold mb-6">Deliveries</h1>

    @if(session('ok'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('ok') }}</div>
    @endif

    @if($deliveries->isEmpty())
        <div class="p-6 border rounded bg-white text-gray-600">
            No deliveries for now.
        </div>
    @else
        <div class="overflow-x-auto bg-white border rounded">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr class="text-left">
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Listing</th>
                        <th class="px-4 py-2">Pickup address</th>
                        <th class="px-4 py-2">Window</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Tracking</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($deliveries as $d)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $d->id }}</td>
                        <td class="px-4 py-2">
                            {{ $d->pickup->wasteItem->title ?? '—' }}
                            <small class="text-gray-400">#{{ $d->pickup_id }}</small>
                        </td>
                        <td class="px-4 py-2">{{ $d->pickup->pickup_address ?? '—' }}</td>
                        <td class="px-4 py-2">
                            <div class="text-xs text-gray-500">Start</div>
                            {{ optional($d->pickup->scheduled_pickup_window_start)->format('Y-m-d H:i') ?? '—' }}
                            <div class="text-xs text-gray-500 mt-1">End</div>
                            {{ optional($d->pickup->scheduled_pickup_window_end)->format('Y-m-d H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                @class([
                                    'bg-amber-100 text-amber-800'  => $d->status === 'scheduled',
                                    'bg-indigo-100 text-indigo-800'=> $d->status === 'assigned',
                                    'bg-sky-100 text-sky-800'      => $d->status === 'in_transit',
                                    'bg-green-100 text-green-800'  => $d->status === 'delivered',
                                ])
                            ">
                                {{ ucfirst(str_replace('_',' ', $d->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-2">{{ $d->tracking_code ?? '—' }}</td>
                        <td class="px-4 py-2">
                            <div class="flex gap-2">
                                @if(in_array($d->status, ['scheduled','assigned']))
                                    <form method="POST" action="{{ route('deliveries.start', $d) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-1 rounded bg-blue-600 text-white text-xs">
                                            Start Delivery
                                        </button>
                                    </form>
                                @endif

                                @if($d->status === 'in_transit')
                                    <form method="POST" action="{{ route('deliveries.delivered', $d) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-1 rounded bg-green-600 text-white text-xs">
                                            Mark Delivered
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $deliveries->links() }}
        </div>
    @endif
</div>
@endsection