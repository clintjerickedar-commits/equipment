{{-- Decorative technology background for the auth pages. Pure SVG, ignores the mouse, never blocks the forms. --}}
<svg class="tech-bg" viewBox="0 0 1600 900" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="tbStreak" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stop-color="#FF0000" stop-opacity="0"/><stop offset=".5" stop-color="#FF2A2A" stop-opacity=".95"/><stop offset="1" stop-color="#FF0000" stop-opacity="0"/>
        </linearGradient>
        <filter id="tbBlur" x="-20%" y="-300%" width="140%" height="700%"><feGaussianBlur stdDeviation="10"/></filter>
    </defs>

    {{-- diagonal red light streaks --}}
    <g transform="rotate(-24 800 450)">
        <rect x="-300" y="210" width="2200" height="54" fill="url(#tbStreak)" opacity=".2" filter="url(#tbBlur)"/>
        <rect x="-300" y="235" width="2200" height="2" fill="url(#tbStreak)" opacity=".85"/>
        <rect x="-300" y="510" width="2200" height="80" fill="url(#tbStreak)" opacity=".15" filter="url(#tbBlur)"/>
        <rect x="-300" y="548" width="2200" height="2" fill="url(#tbStreak)" opacity=".6"/>
        <rect x="-300" y="690" width="2200" height="1.5" fill="url(#tbStreak)" opacity=".4"/>
        <rect x="-300" y="95" width="2200" height="1.5" fill="url(#tbStreak)" opacity=".35"/>
    </g>

    {{-- glowing circuit lines with nodes --}}
    <g fill="none" stroke="#FF2A2A" stroke-opacity=".38" stroke-width="1.4">
        <polyline class="tb-line" points="0,640 180,640 240,580 520,580 580,520 760,520"/>
        <polyline class="tb-line" points="1600,180 1420,180 1360,240 1180,240 1120,300 980,300"/>
        <polyline class="tb-line" points="0,120 140,120 200,180 380,180"/>
        <polyline class="tb-line" points="1600,780 1380,780 1320,720 1100,720"/>
    </g>
    <g fill="#FF3A3A" opacity=".7">
        <circle cx="760" cy="520" r="4"/><circle cx="980" cy="300" r="4"/><circle cx="380" cy="180" r="4"/><circle cx="1100" cy="720" r="4"/>
        <circle cx="520" cy="580" r="2.5"/><circle cx="1180" cy="240" r="2.5"/>
    </g>

    {{-- server rack (bottom right) --}}
    <g transform="translate(1340 500)" stroke="#fff" stroke-opacity=".1" fill="#fff" fill-opacity=".02" stroke-width="1.2">
        <rect width="190" height="340" rx="10"/>
        @for($i = 0; $i < 6; $i++)
            <rect x="18" y="{{ 18 + $i * 52 }}" width="154" height="36" rx="5"/>
            <circle class="tb-led" style="animation-delay: {{ $i * .5 }}s" cx="36" cy="{{ 36 + $i * 52 }}" r="3.5" fill="#FF2A2A" stroke="none"/>
            <rect x="60" y="{{ 31 + $i * 52 }}" width="70" height="4" rx="2" stroke="none" fill-opacity=".08"/>
            <rect x="60" y="{{ 40 + $i * 52 }}" width="46" height="4" rx="2" stroke="none" fill-opacity=".05"/>
        @endfor
    </g>

    {{-- second small rack (top left) --}}
    <g transform="translate(70 40)" stroke="#fff" stroke-opacity=".08" fill="#fff" fill-opacity=".015" stroke-width="1.2">
        <rect width="120" height="170" rx="8"/>
        @for($i = 0; $i < 4; $i++)
            <rect x="12" y="{{ 12 + $i * 38 }}" width="96" height="26" rx="4"/>
            <circle class="tb-led" style="animation-delay: {{ 1 + $i * .6 }}s" cx="26" cy="{{ 25 + $i * 38 }}" r="3" fill="#FF2A2A" stroke="none"/>
        @endfor
    </g>

    {{-- equipment outlines: laptop, camera, projector --}}
    <g fill="none" stroke="#fff" stroke-opacity=".12" stroke-width="1.5">
        <g transform="translate(120 700)"><rect width="150" height="96" rx="8"/><rect x="-18" y="96" width="186" height="10" rx="5"/></g>
        <g transform="translate(1160 80)"><rect x="0" y="16" width="120" height="76" rx="12"/><circle cx="60" cy="54" r="24"/><rect x="22" y="4" width="34" height="14" rx="4"/></g>
        <g transform="translate(640 770)"><rect width="150" height="52" rx="12"/><circle cx="108" cy="26" r="16"/></g>
    </g>
</svg>
