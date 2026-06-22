@switch($name)
    @case('id-card')
    @case('document')
        <path d="M16 10h2" />
        <path d="M16 14h2" />
        <path d="M6.17 15a3 3 0 0 1 5.66 0" />
        <circle cx="9" cy="11" r="2" />
        <rect x="2" y="5" width="20" height="14" rx="2" />
        @break

    @case('lock-keyhole')
    @case('lock')
        <circle cx="12" cy="16" r="1" />
        <rect x="3" y="10" width="18" height="12" rx="2" />
        <path d="M7 10V7a5 5 0 0 1 10 0v3" />
        @break

    @case('eye')
        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
        <circle cx="12" cy="12" r="3" />
        @break

    @case('eye-off')
        <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49" />
        <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242" />
        <path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143" />
        <path d="m2 2 20 20" />
        @break

    @case('home')
        <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
        <path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
        @break

    @case('chart')
        <path d="M3 3v16a2 2 0 0 0 2 2h16" />
        <path d="M18 17V9" />
        <path d="M13 17V5" />
        <path d="M8 17v-3" />
        @break

    @case('warehouse')
        <path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z" />
        <path d="M6 18h12" />
        <path d="M6 14h12" />
        <path d="M6 10h12" />
        @break

    @case('menu')
        <path d="M4 12h16" />
        <path d="M4 18h16" />
        <path d="M4 6h16" />
        @break

    @case('close')
        <path d="M18 6 6 18" />
        <path d="m6 6 12 12" />
        @break

    @case('logout')
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
        <path d="m16 17 5-5-5-5" />
        <path d="M21 12H9" />
        @break

    @case('egg')
        <path d="M12 22c6.23-.05 7.87-5.43 7.8-10.5C19.78 6.55 16.54 2 12 2S4.22 6.55 4.2 11.5C4.13 16.57 5.77 21.95 12 22Z" />
        @break

    @case('users')
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
        @break

    @case('building')
        <path d="M12 10h.01" />
        <path d="M12 14h.01" />
        <path d="M12 6h.01" />
        <path d="M16 10h.01" />
        <path d="M16 14h.01" />
        <path d="M16 6h.01" />
        <path d="M8 10h.01" />
        <path d="M8 14h.01" />
        <path d="M8 6h.01" />
        <path d="M9 22v-3h6v3" />
        <path d="M4 10V4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6" />
        @break

    @case('layers')
        <path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83z" />
        <path d="M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 12" />
        <path d="M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 17" />
        @break

    @case('file-bar-chart')
        <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z" />
        <path d="M14 2v5a1 1 0 0 0 1 1h5" />
        <path d="M8 18v-2" />
        <path d="M12 18v-4" />
        <path d="M16 18v-6" />
        @break

    @case('clipboard-list')
        <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
        <path d="M12 11h4" />
        <path d="M12 16h4" />
        <path d="M8 11h.01" />
        <path d="M8 16h.01" />
        @break

    @case('bell')
        <path d="M10.268 21a2 2 0 0 0 3.464 0" />
        <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326" />
        @break

    @case('trending-up')
        <path d="M16 7h6v6" />
        <path d="m22 7-8.5 8.5-5-5L2 17" />
        @break

    @case('calendar')
        <path d="M8 2v4" />
        <path d="M16 2v4" />
        <rect width="18" height="18" x="3" y="4" rx="2" />
        <path d="M3 10h18" />
        @break

    @case('clock')
        <circle cx="12" cy="12" r="10" />
        <path d="M12 6v6l4 2" />
        @break

    @case('arrow-right')
        <path d="M5 12h14" />
        <path d="m12 5 7 7-7 7" />
        @break

    @case('chevron-down')
        <path d="m6 9 6 6 6-6" />
        @break

    @case('chevron-right')
        <path d="m9 18 6-6-6-6" />
        @break

    @case('check-circle')
        <circle cx="12" cy="12" r="10" />
        <path d="m9 12 2 2 4-4" />
        @break

    @case('plus')
        <path d="M5 12h14" />
        <path d="M12 5v14" />
        @break

    @case('hand-wave')
        <path d="M7 11V4a2 2 0 0 1 4 0v2" />
        <path d="M11 6V3a2 2 0 0 1 4 0v4" />
        <path d="M15 7V5a2 2 0 0 1 4 0v8a6 6 0 0 1-6 6h-2a6 6 0 0 1-6-6V9a2 2 0 0 1 4 0v2" />
        @break

    @case('panel-left')
        <rect width="18" height="18" x="3" y="3" rx="2" />
        <path d="M9 3v18" />
        @break

    @case('shield')
        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
        @break

    @default
        <circle cx="12" cy="12" r="10" />
        <path d="M12 16v-4" />
        <path d="M12 8h.01" />
@endswitch
