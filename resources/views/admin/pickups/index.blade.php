@extends('layouts.admin')

@section('title', 'Manage Pickups')

@section('admin-content')
<div class="admin-topbar">
    <div>
        <h1>Manage Pickups</h1>
        <div class="tb-sub">View, filter, and manage all pickups</div>
    </div>
    <div class="tb-right">
        <form method="GET" action="{{ route('admin.pickups.index') }}">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input
                type="search"
                name="q"
                placeholder="Search by waste item, address, status, tracking..."
                value="{{ request('q') }}"
                class="search-input"
            />
        </form>
    </div>
</div>

<section class="admin-grid">
    <div class="a-card wide">
        <div class="a-title"><i class="fa-solid fa-truck"></i> All Pickups</div>

        @if(session('ok'))
            <div class="a-flash is-ok">{{ session('ok') }}</div>
        @endif

        @if($pickups->isEmpty())
            <p class="mt-2 text-gray-500">No pickups found.</p>
        @else
            <table class="a-table">
                <thead>
                    <tr>
                        <th>Waste Item</th>
                        <th>Address</th>
                        <th>Window</th>
                        <th>Status</th>
                        <th>Tracking</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pickups as $p)
                        <tr id="row-{{ $p->id }}">
                            <td>
                                <div class="user-info">
                                    <span class="avatar sm" style="--h: {{ (($p->waste_item_id ?? 1) * 47) % 360 }};">
                                        {{ strtoupper(substr($p->wasteItem->title ?? '—', 0, 2)) }}
                                    </span>
                                    {{ $p->wasteItem->title ?? '—' }}
                                </div>
                            </td>

                            <td>{{ $p->pickup_address }}</td>

                            <td>
                                <div class="text-xs text-gray-500">Start</div>
                                {{ optional($p->scheduled_pickup_window_start)->format('Y-m-d H:i') ?? '—' }}
                                <div class="mt-1 text-xs text-gray-500">End</div>
                                {{ optional($p->scheduled_pickup_window_end)->format('Y-m-d H:i') ?? '—' }}
                            </td>

                            <td>
                                <span class="badge
                                    @if($p->status==='scheduled')    is-amber
                                    @elseif($p->status==='assigned') is-blue
                                    @elseif($p->status==='in_transit') is-sky
                                    @elseif($p->status==='picked')   is-green
                                    @elseif($p->status==='failed')   is-red
                                    @else                             is-gray @endif">
                                    {{ ucfirst(str_replace('_',' ', $p->status)) }}
                                </span>
                            </td>

                            <td>{{ $p->tracking_code ?? '—' }}</td>
                            <td>{{ $p->created_at->format('M d, Y') }}</td>

                            <td class="actions text-right">
                                <div class="action-group justify-end">
                                    {{-- View --}}
                                    <a href="{{ route('admin.pickups.show', $p) }}"
                                       class="icon-btn is-blue" title="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.pickups.edit', $p) }}"
                                       class="icon-btn is-amber" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    {{-- Delete (AJAX, on reste sur la page) --}}
                                    <button class="icon-btn is-red js-del"
                                            title="Delete"
                                            data-url="{{ route('admin.pickups.destroy', $p) }}"
                                            data-row="#row-{{ $p->id }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="admin-pagination mt-4">
                {{ $pickups->onEachSide(1)->links('pagination::simple-tailwind') }}
            </div>
        @endif
    </div>
</section>
@endsection

{{-- IMPORTANT: utiliser la bonne stack pour le layout admin --}}
@push('admin-scripts')
<script>
(function () {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-del');
    if (!btn) return;

    if (!confirm('Delete this pickup? This cannot be undone.')) return;

    const url = btn.dataset.url;
    const rowSel = btn.dataset.row;

    try {
      const res = await fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      if (res.status === 204 || res.ok) {
        // enlève la ligne sans recharger
        const row = document.querySelector(rowSel);
        if (row) row.remove();
      } else {
        const msg = await res.text();
        alert('Delete failed.\n' + msg);
      }
    } catch (err) {
      console.error(err);
      alert('Network error while deleting.');
    }
  });
})();
</script>
@endpush