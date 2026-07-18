@props(['route', 'icon', 'label'])

<li class="nav-item">
    <a href="{{ route($route) }}" @class(['nav-link', 'active' => request()->routeIs($route, Str::before($route, '.').'.*')])>
        <i class="nav-icon {{ $icon }}"></i>
        <p>{{ $label }}</p>
    </a>
</li>
