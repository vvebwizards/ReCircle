@extends('layouts.admin')

@section('title','Manage Deliveries')

@section('admin-content')
<div class="admin-topbar">
    <div>
        <h1>Manage Deliveries</h1>
        <div class="tb-sub">View, filter, and manage all deliveries</div>
    </div>
    <div class="tb-right">
        <form method="GET"
              action="{{ $tab === 'completed' ? route('admin.deliveries.completed') : route('admin.deliveries.index') }}">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search"
                   name="q"
                   placeholder="Search tracking, listing, address..."
                   value="{{ $q }}"
                   class="search-input" />
        </form>
    </div>
</div>

{{-- Onglets --}}
<div class="mb-4 flex items-center gap-2">
    <a href="{{ route('admin.deliveries.index') }}"
       class="px-3 py-1 rounded {{ $tab==='active' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">
        Active
    </a>
    <a href="{{ route('admin.deliveries.completed') }}"
       class="px-3 py-1 rounded {{ $tab==='completed' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">
        Completed
    </a>
</div>

<section class="admin-grid">
    <div class="a-card wide">
        <div class="a-title"><i class="fa-solid fa-truck-fast"></i> {{ $tab==='completed' ? 'Completed Deliveries' : 'Active Deliveries' }}</div>

        @if($deliveries->isEmpty())
            <p class="mt-2 text-gray-500">No deliveries found.</p>
        @else
            <table class="a-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Listing</th>
                        <th>Pickup address</th>
                        <th>Window</th>
                        <th>Courier</th>
                        <th>Status</th>
                        <th>Tracking</th>
                        <th>{{ $tab==='completed' ? 'Arrived' : 'Created' }}</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deliveries as $d)
                        <tr>
                            <td>{{ $d->id }}</td>
                            <td>
                                <div class="user-info">
                                    <span class="avatar sm" style="--h: {{ (($d->pickup->waste_item_id ?? 1)*47)%360 }};">
                                        {{ strtoupper(substr($d->pickup->wasteItem->title ?? '—',0,2)) }}
                                    </span>
                                    {{ $d->pickup->wasteItem->title ?? '—' }}
                                    <small class="text-gray-400 ml-1">#{{ $d->pickup_id }}</small>
                                </div>
                            </td>
                            <td>{{ $d->pickup->pickup_address ?? '—' }}</td>
                            <td>
                                <div class="text-xs text-gray-500">Start</div>
                                {{ optional($d->pickup->scheduled_pickup_window_start)->format('Y-m-d H:i') ?? '—' }}
                                <div class="mt-1 text-xs text-gray-500">End</div>
                                {{ optional($d->pickup->scheduled_pickup_window_end)->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td>
                                @if($d->courier)
                                    {{ $d->courier->name }}
                                    <div class="text-xs text-gray-500">{{ $d->courier->email }}</div>
                                @else
                                    <span class="badge is-gray">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge
                                    @if($d->status==='scheduled')     is-amber
                                    @elseif($d->status==='assigned')  is-blue
                                    @elseif($d->status==='in_transit')is-sky
                                    @elseif($d->status==='delivered') is-green
                                    @elseif($d->status==='failed')    is-red
                                    @else                              is-gray @endif">
                                    {{ ucfirst(str_replace('_',' ', $d->status)) }}
                                </span>
                                @if($d->status==='delivered' && $d->arrived_hub_at)
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $d->arrived_hub_at->format('Y-m-d H:i') }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ $d->tracking_code ?? '—' }}</td>
                            <td>
                                @if($tab==='completed')
                                    {{ optional($d->arrived_hub_at)->format('Y-m-d H:i') ?? '—' }}
                                @else
                                    {{ $d->created_at->format('Y-m-d') }}
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.deliveries.show',$d) }}" class="icon-btn is-blue" title="View">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                {{-- (Optionnel) Ajouter ici Edit/Delete si tu veux que l’admin modifie/supprime --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="admin-pagination mt-4">
                {{ $deliveries->onEachSide(1)->links('pagination::simple-tailwind') }}
            </div>
        @endif
    </div>
</section>
@endsection