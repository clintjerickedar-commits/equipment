@php
    $name = $item->name;
    $product = strtolower($name);
    $kind = match (true) {
        str_contains($product, 'ball') => 'sports',
        str_contains($product, 'camera') => 'camera',
        str_contains($product, 'tripod') => 'tripod',
        str_contains($product, 'ring light') => 'ring-light',
        str_contains($product, 'led light') => 'led-light',
        str_contains($product, 'speaker') => 'speaker',
        str_contains($product, 'laptop charger') => 'charger',
        str_contains($product, 'laptop'), str_contains($product, 'keyboard'),
        str_contains($product, 'mouse'), str_contains($product, 'tablet') => 'computer',
        str_contains($product, 'calculator'), str_contains($product, 'multimeter'),
        str_contains($product, 'breadboard'), str_contains($product, 'component') => 'electronics',
        str_contains($product, 'drawing board'), str_contains($product, 'geometry'),
        str_contains($product, 'clipboard') => 'school',
        default => 'presentation',
    };
@endphp
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 420" role="img" aria-label="{{ $name }}">
    <defs>
        <linearGradient id="background" x2="1" y2="1">
            <stop stop-color="#281216"/>
            <stop offset="1" stop-color="#111820"/>
        </linearGradient>
        <linearGradient id="surface" x2="0" y2="1">
            <stop stop-color="#555b66"/>
            <stop offset="1" stop-color="#20252c"/>
        </linearGradient>
        <radialGradient id="lens">
            <stop stop-color="#6ab1c5"/>
            <stop offset=".55" stop-color="#193b50"/>
            <stop offset="1" stop-color="#090d12"/>
        </radialGradient>
    </defs>
    <rect width="640" height="420" rx="24" fill="url(#background)"/>
    <circle cx="320" cy="185" r="137" fill="#ff25381b"/>
    <path d="M0 316h640v104H0z" fill="#080b10" opacity=".55"/>

    @if($kind === 'sports')
        <circle cx="320" cy="184" r="104" fill="#f3f0e9"/>
        @if(str_contains($product, 'basketball'))
            <circle cx="320" cy="184" r="101" fill="#e87928"/>
            <path d="M220 184h200M320 83v202M250 112c50 45 50 99 0 144m140-144c-50 45-50 99 0 144" fill="none" stroke="#43251c" stroke-width="8"/>
        @elseif(str_contains($product, 'soccer'))
            <path d="m320 113 31 23-12 37h-38l-12-37zm-68 33 37 2 12 35-30 20-30-22zm136 0-37 2-12 35 30 20 30-22zM282 216l30-22 30 22-12 36h-36zm-67-58 26-11m179 11-26-11m-73 103v-15" fill="#252a32" stroke="#252a32" stroke-width="4" stroke-linejoin="round"/>
        @else
            <path d="M244 105c51 38 101 118 152 156M396 105c-51 38-101 118-152 156M220 184h200" fill="none" stroke="#d83a4b" stroke-width="9"/>
        @endif
        <path d="M250 113c27-31 70-41 107-26" fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="10" stroke-linecap="round"/>
    @elseif($kind === 'camera')
        <rect x="177" y="119" width="286" height="169" rx="29" fill="url(#surface)" stroke="#77808b" stroke-width="5"/>
        <path d="m222 119 23-33h91l23 33" fill="#323840" stroke="#737c86" stroke-width="5" stroke-linejoin="round"/>
        <rect x="384" y="138" width="40" height="22" rx="5" fill="#f5f0d7"/>
        <circle cx="320" cy="204" r="70" fill="#171c24" stroke="#89929c" stroke-width="8"/>
        <circle cx="320" cy="204" r="51" fill="url(#lens)" stroke="#293542" stroke-width="8"/>
        <circle cx="302" cy="185" r="12" fill="#d9fbff" opacity=".65"/>
        <circle cx="421" cy="180" r="7" fill="#e74a54"/>
    @elseif($kind === 'computer')
        @if(str_contains($product, 'mouse'))
            <path d="M320 89c-48 0-78 35-78 85v46c0 59 32 94 78 94s78-35 78-94v-46c0-50-30-85-78-85z" fill="url(#surface)" stroke="#9098a2" stroke-width="6"/>
            <path d="M320 92v78m-78 5h156" fill="none" stroke="#161b22" stroke-width="7"/>
            <rect x="310" y="119" width="20" height="37" rx="10" fill="#f0454f"/>
        @else
            <rect x="190" y="74" width="260" height="193" rx="15" fill="#11161d" stroke="#838d98" stroke-width="8"/>
            <rect x="204" y="88" width="232" height="164" rx="5" fill="#182c3a"/>
            <path d="M204 224h232v28H204z" fill="#792b35"/>
            <path d="m148 281 24-19h296l24 19-14 19H162z" fill="url(#surface)" stroke="#8c959f" stroke-width="5" stroke-linejoin="round"/>
            <path d="M271 278h98" stroke="#aab1b9" stroke-width="5" stroke-linecap="round"/>
        @endif
    @elseif($kind === 'tripod')
        <rect x="265" y="91" width="110" height="100" rx="13" fill="url(#surface)" stroke="#8d96a0" stroke-width="5"/>
        <circle cx="320" cy="141" r="28" fill="url(#lens)" stroke="#a2aab2" stroke-width="5"/>
        <path d="M320 192v28m0 0-93 99m93-99 94 99m-94-99v115" fill="none" stroke="#aeb5bd" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/>
    @elseif($kind === 'ring-light')
        <circle cx="320" cy="170" r="91" fill="none" stroke="#f6eee0" stroke-width="23"/>
        <circle cx="320" cy="170" r="69" fill="#17212b"/>
        <path d="M320 263v31m0 0-68 38m68-38 68 38m-68-38v43" fill="none" stroke="#aeb5bd" stroke-width="10" stroke-linecap="round"/>
        <circle cx="320" cy="170" r="10" fill="#ed4751"/>
    @elseif($kind === 'led-light')
        <path d="M259 108h122l-18 126H277z" fill="#e8e4d3" stroke="#9299a1" stroke-width="6" stroke-linejoin="round"/>
        <rect x="282" y="235" width="76" height="30" rx="9" fill="#414954"/>
        <path d="M320 265v27m0 0-62 38m62-38 62 38m-62-38v45" fill="none" stroke="#aeb5bd" stroke-width="9" stroke-linecap="round"/>
        <path d="M255 83h-30m185 0h-30M320 61V38" stroke="#f5d875" stroke-width="8" stroke-linecap="round"/>
    @elseif($kind === 'speaker')
        <rect x="236" y="69" width="168" height="244" rx="25" fill="url(#surface)" stroke="#818b96" stroke-width="6"/>
        <circle cx="320" cy="157" r="47" fill="#171b22" stroke="#a2abb5" stroke-width="7"/>
        <circle cx="320" cy="157" r="18" fill="#4a5360"/>
        <circle cx="320" cy="251" r="32" fill="#171b22" stroke="#a2abb5" stroke-width="6"/>
        <circle cx="320" cy="251" r="11" fill="#4a5360"/>
        <circle cx="320" cy="92" r="5" fill="#e84a53"/>
    @elseif($kind === 'charger')
        <rect x="225" y="115" width="190" height="145" rx="22" fill="url(#surface)" stroke="#858e98" stroke-width="6"/>
        <path d="M268 115V87m104 28V87" stroke="#b8bec5" stroke-width="10" stroke-linecap="round"/>
        <path d="M415 186c73 0 80 66 137 66" fill="none" stroke="#dadde0" stroke-width="12" stroke-linecap="round"/>
        <rect x="534" y="237" width="27" height="30" rx="5" fill="#89929c"/>
        <circle cx="320" cy="186" r="26" fill="#151a20"/>
    @elseif($kind === 'electronics')
        @if(str_contains($product, 'breadboard') || str_contains($product, 'component'))
            <rect x="168" y="101" width="304" height="183" rx="18" fill="#ece8db" stroke="#979b9d" stroke-width="6"/>
            @for($row = 0; $row < 5; $row++)
                @for($col = 0; $col < 13; $col++)
                    <circle cx="{{ 198 + $col * 20 }}" cy="{{ 143 + $row * 27 }}" r="4" fill="#46525e"/>
                @endfor
            @endfor
            <path d="M227 110v27m82-27v27m83-27v27" stroke="#d83e4a" stroke-width="6"/>
            <path d="M247 284v23m146-23v23" stroke="#dedbd1" stroke-width="10"/>
        @else
            <rect x="233" y="60" width="174" height="269" rx="24" fill="url(#surface)" stroke="#8b949f" stroke-width="6"/>
            <rect x="257" y="84" width="126" height="60" rx="8" fill="#9db6a0"/>
            <g fill="#242a32" stroke="#9ba3ab" stroke-width="2">
                <rect x="258" y="163" width="33" height="27" rx="6"/><rect x="303" y="163" width="33" height="27" rx="6"/><rect x="348" y="163" width="33" height="27" rx="6"/>
                <rect x="258" y="200" width="33" height="27" rx="6"/><rect x="303" y="200" width="33" height="27" rx="6"/><rect x="348" y="200" width="33" height="27" rx="6"/>
                <rect x="258" y="237" width="33" height="27" rx="6"/><rect x="303" y="237" width="33" height="27" rx="6"/><rect x="348" y="237" width="33" height="27" rx="6"/>
            </g>
            <circle cx="320" cy="294" r="10" fill="#ed4751"/>
        @endif
    @elseif($kind === 'school')
        @if(str_contains($product, 'geometry'))
            <path d="M220 285 320 91l100 194z" fill="#e9e5d9" stroke="#aeb4bb" stroke-width="8" stroke-linejoin="round"/>
            <path d="m252 268 68-132 68 132z" fill="#ed4652" opacity=".88"/>
            <circle cx="320" cy="245" r="47" fill="none" stroke="#f3c75e" stroke-width="8"/>
        @else
            <rect x="215" y="58" width="210" height="272" rx="18" fill="#a96c3e" stroke="#d6aa73" stroke-width="8"/>
            <rect x="238" y="85" width="164" height="221" rx="6" fill="#f0eee6"/>
            @if(str_contains($product, 'clipboard'))
                <rect x="279" y="47" width="82" height="32" rx="10" fill="#c6cbd0"/>
                <path d="M260 128h116m-116 45h116m-116 45h116" stroke="#bac1c8" stroke-width="6"/>
                <path d="m254 126 7 7 13-16m-20 54 7 7 13-16m-20 54 7 7 13-16" fill="none" stroke="#e24550" stroke-width="5"/>
            @else
                <path d="M262 279V111h116v168z" fill="#dce4e6" stroke="#53616c" stroke-width="5"/>
                <path d="M276 264 365 129M276 129l89 135" stroke="#e34852" stroke-width="5"/>
                <path d="M250 287h145" stroke="#f2d16e" stroke-width="6"/>
            @endif
        @endif
    @else
        @if(str_contains($product, 'hdmi cable'))
            <path d="M214 155c-80 0-83 111 0 111h52c78 0 77-113 0-113h-20" fill="none" stroke="#343b45" stroke-width="19" stroke-linecap="round"/>
            <path d="M214 155h-52v39h52m104-41h48v38h-48" fill="#929aa3" stroke="#d0d4d8" stroke-width="5" stroke-linejoin="round"/>
            <path d="M162 164v20m152-17v17" stroke="#252a31" stroke-width="5"/>
        @elseif(str_contains($product, 'presenter'))
            <rect x="289" y="78" width="62" height="238" rx="30" fill="url(#surface)" stroke="#9ca4ad" stroke-width="6"/>
            <circle cx="320" cy="112" r="13" fill="#ed4751"/>
            <rect x="306" y="148" width="28" height="70" rx="13" fill="#1a2028"/>
            <circle cx="320" cy="252" r="7" fill="#8cae9a"/>
        @else
            <rect x="166" y="63" width="308" height="225" rx="13" fill="#303842" stroke="#8d96a0" stroke-width="7"/>
            <rect x="185" y="82" width="270" height="187" rx="4" fill="#e9e6da"/>
            <path d="M230 226h180m-150-44h120m-95-44h70" stroke="#b6bec2" stroke-width="8" stroke-linecap="round"/>
            @if(str_contains($product, 'projector'))
                <rect x="251" y="291" width="138" height="42" rx="13" fill="url(#surface)" stroke="#8d96a0" stroke-width="5"/>
                <circle cx="320" cy="312" r="11" fill="url(#lens)"/>
                <path d="M298 291 246 269m96 22 52-22" stroke="#f2d76c" stroke-width="3" opacity=".7"/>
            @else
                <path d="M206 297h228" stroke="#9ca4ad" stroke-width="8" stroke-linecap="round"/>
                <circle cx="320" cy="310" r="12" fill="#ed4751"/>
            @endif
        @endif
    @endif

    <rect x="24" y="354" width="592" height="42" rx="12" fill="#0b0e13" opacity=".78"/>
    <text x="320" y="383" text-anchor="middle" fill="#fff" font-family="Arial, sans-serif" font-size="25" font-weight="700" textLength="550" lengthAdjust="spacingAndGlyphs">{{ $name }}</text>
</svg>
