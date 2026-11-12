{{-- Decorative equipment illustration (inline SVG, no external images, sharp at any size) --}}
<svg viewBox="0 0 480 360" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Equipment borrowing illustration" class="{{ $class ?? '' }}" style="width:100%;height:auto;display:block">
    <defs>
        <linearGradient id="eqPanel" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".11"/><stop offset="1" stop-color="#fff" stop-opacity=".03"/></linearGradient>
        <linearGradient id="eqScreen" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FF2A2A"/><stop offset="1" stop-color="#8B0000"/></linearGradient>
    </defs>
    <ellipse cx="240" cy="338" rx="205" ry="16" fill="#000" opacity=".28"/>

    {{-- back panel: pills below sit exactly on its top-right and bottom-left edges --}}
    <rect x="30" y="40" width="420" height="260" rx="24" fill="url(#eqPanel)" stroke="#fff" stroke-opacity=".1"/>

    {{-- laptop --}}
    <rect x="70" y="84" width="200" height="130" rx="10" fill="#141414"/>
    <rect x="78" y="92" width="184" height="114" rx="6" fill="url(#eqScreen)"/>
    <rect x="92" y="108" width="80" height="9" rx="4.5" fill="#fff" opacity=".95"/>
    <rect x="92" y="126" width="128" height="6" rx="3" fill="#fff" opacity=".55"/>
    <rect x="92" y="140" width="104" height="6" rx="3" fill="#fff" opacity=".4"/>
    <rect x="92" y="164" width="58" height="26" rx="8" fill="#fff"/>
    <rect x="50" y="214" width="240" height="14" rx="7" fill="#E5E5E5"/>
    <rect x="140" y="214" width="60" height="6" rx="3" fill="#A3A3A3"/>

    {{-- camera --}}
    <rect x="322" y="128" width="40" height="18" rx="6" fill="#E5E5E5"/>
    <rect x="300" y="140" width="130" height="86" rx="14" fill="#fff"/>
    <circle cx="365" cy="184" r="28" fill="#0A0A0A"/>
    <circle cx="365" cy="184" r="18" fill="#D90000"/>
    <circle cx="358" cy="177" r="5" fill="#fff" opacity=".7"/>
    <circle cx="410" cy="158" r="4" fill="#F59E0B"/>

    {{-- projector --}}
    <rect x="190" y="242" width="150" height="52" rx="12" fill="#F4F4F4"/>
    <circle cx="300" cy="268" r="17" fill="#0A0A0A"/>
    <circle cx="300" cy="268" r="10" fill="#FF5C5C"/>
    <rect x="208" y="258" width="48" height="6" rx="3" fill="#A3A3A3"/>
    <rect x="208" y="272" width="32" height="6" rx="3" fill="#E5E5E5"/>

@if($pills ?? true)
    {{-- "Approved" pill: right edge = panel right edge, centred on the top edge --}}
    <g transform="translate(310 20)">
        <rect width="140" height="40" rx="20" fill="#fff"/>
        <circle cx="23" cy="20" r="12" fill="#16A34A"/>
        <path d="M17.5 20.5l3.6 3.6 6.4-7" stroke="#fff" stroke-width="2.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        <text x="45" y="25.5" font-family="Plus Jakarta Sans, sans-serif" font-size="14" font-weight="700" fill="#0A0A0A">Approved</text>
    </g>

    {{-- "Due in 3d" pill: left edge = panel left edge, centred on the bottom edge --}}
    <g transform="translate(30 282)">
        <rect width="118" height="36" rx="18" fill="#0A0A0A" stroke="#D90000" stroke-width="2"/>
        <circle cx="20" cy="18" r="8" fill="#F59E0B"/>
        <text x="37" y="23" font-family="Plus Jakarta Sans, sans-serif" font-size="13" font-weight="700" fill="#fff">Due in 3d</text>
    </g>
@endif
</svg>
