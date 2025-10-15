@extends('layouts.app')

@section('title', 'Pickup #'.$pickup->id)

@section('content')

<div class="container mx-auto px-4 py-8 max-w-4xl pt-28">
    @if(session('ok'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('ok') }}</div>
    @endif

  

    {{-- carte résumé --}}
    <div class="bg-white rounded-lg shadow border divide-y">
        <div class="p-4">
            <div class="text-sm text-gray-500">Listing</div>
            <div class="text-lg font-medium">
                {{ $pickup->wasteItem->title ?? '—' }}
                <span class="ml-2 text-gray-400">(#{{ $pickup->waste_item_id }})</span>
            </div>
        </div>

        <div class="p-4 grid sm:grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-gray-500">Pickup address</div>
                <div class="font-medium">{{ $pickup->pickup_address }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Status</div>
                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold
                    @class([
                        'bg-yellow-100 text-yellow-800' => $pickup->status==='scheduled',
                        'bg-indigo-100 text-indigo-800' => $pickup->status==='assigned',
                        'bg-blue-100 text-blue-800'     => $pickup->status==='in_transit',
                        'bg-green-100 text-green-800'   => $pickup->status==='picked',
                        'bg-red-100 text-red-800'       => $pickup->status==='failed',
                        'bg-gray-100 text-gray-800'     => $pickup->status==='cancelled',
                    ])
                ">
                    {{ ucfirst(str_replace('_',' ', $pickup->status)) }}
                </span>
            </div>
        </div>

        <div class="p-4 grid sm:grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-gray-500">Window start</div>
                <div class="font-medium">
                    {{ optional($pickup->scheduled_pickup_window_start)->format('Y-m-d H:i') ?? '—' }}
                </div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Window end</div>
                <div class="font-medium">
                    {{ optional($pickup->scheduled_pickup_window_end)->format('Y-m-d H:i') ?? '—' }}
                </div>
            </div>
        </div>

        <div class="p-4 grid sm:grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-gray-500">Tracking code</div>
                <div class="font-medium">{{ $pickup->tracking_code ?? '—' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Courier</div>
                <div class="font-medium">{{ $pickup->courier_id ? '#'.$pickup->courier_id : '—' }}</div>
            </div>
        </div>

        <div class="p-4">
            <div class="text-sm text-gray-500">Notes</div>
            <div class="whitespace-pre-line">{{ $pickup->notes ?? '—' }}</div>
        </div>

        <div class="p-4 text-sm text-gray-500 flex justify-between">
            <div>Created: <span class="font-medium text-gray-700">{{ $pickup->created_at }}</span></div>
            <div>Updated: <span class="font-medium text-gray-700">{{ $pickup->updated_at }}</span></div>
        </div>
    </div>
  <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Pickup #{{ $pickup->id }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('pickups.edit', $pickup) }}"
               class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Edit</a>

            <form method="POST" action="{{ route('pickups.destroy', $pickup) }}"
                  onsubmit="return confirm('Delete this pickup? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button class="px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700">
                    Delete
                </button>
            </form>
        </div>
    </div>
    <div class="mt-6">
        <a href="{{ route('pickups.index') }}" class="text-indigo-600 hover:underline">&larr; Back to list</a>
    </div>
</div>
@endsection