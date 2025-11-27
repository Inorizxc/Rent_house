@php
    $p = $page;
    $N = $pages;
    $items = [];

    if ($N <= 7) {
        $items = range(1, $N);
    } elseif ($p <= 4) {
        $items = [1, 2, 3, 4, 5, 'dots', $N];
    } elseif ($p >= $N - 3) {
        $items = [1, 'dots', $N-4, $N-3, $N-2, $N-1, $N];
    } else {
        $items = [1, 'dots', $p-1, $p, $p+1, 'dots', $N];
    }
@endphp

@if ($pages > 1)
    <div class="pagination">
        <a href="?type={{ $type }}&page={{ max(1, $p-1) }}&per={{ $limit }}"
           class="{{ $p==1 ? 'disabled' : '' }}">&lsaquo;</a>

        @foreach ($items as $it)
            @if ($it === 'dots')
                <button type="button" class="pag-ellipsis" data-total="{{ $N }}">…</button>
            @else
                <a href="?type={{ $type }}&page={{ $it }}&per={{ $limit }}"
                   class="{{ $it==$p ? 'active' : '' }}">{{ $it }}</a>
            @endif
        @endforeach

        <a href="?type={{ $type }}&page={{ min($N, $p+1) }}&per={{ $limit }}"
           class="{{ $p==$N ? 'disabled' : '' }}">&rsaquo;</a>
    </div>
@endif

<script>
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('pag-ellipsis')) {
        const total = parseInt(e.target.getAttribute('data-total') || '1', 10);
        const input = prompt(`Введите номер страницы (1–${total})`);
        if (!input) return;
        let p = parseInt(input, 10);
        if (isNaN(p)) { alert('Введите число.'); return; }
        if (p < 1) p = 1;
        if (p > total) p = total;

        const params = new URLSearchParams(window.location.search);
        params.set('type', '{{ $type }}');
        params.set('per', String({{ $limit }}));
        params.set('page', String(p));
        window.location.search = params.toString();
    }
});
</script>

