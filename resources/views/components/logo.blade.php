@props(['size' => 44])

<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Juaracatur">
    <defs>
        <linearGradient id="dcBadge" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#3a0000"/>
            <stop offset="0.55" stop-color="#161616"/>
            <stop offset="1" stop-color="#000000"/>
        </linearGradient>
        <linearGradient id="dcKnight" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#ffe3b3"/>
            <stop offset="0.55" stop-color="#e0463a"/>
            <stop offset="1" stop-color="#8f1c14"/>
        </linearGradient>
    </defs>

    <polygon points="60,3 113,31.5 113,88.5 60,117 7,88.5 7,31.5" fill="url(#dcBadge)" stroke="#e0463a" stroke-width="4"/>
    <polygon points="60,3 113,31.5 113,88.5 60,117 7,88.5 7,31.5" fill="none" stroke="#000" stroke-width="1.5" opacity="0.5"/>

    <path d="M16,92 L42,66 L34,55 L64,22" stroke="#ff6a5a" stroke-width="3" fill="none" opacity="0.55" stroke-linecap="round"/>

    <text x="60" y="84" font-size="66" text-anchor="middle" font-family="Georgia, 'Times New Roman', serif" font-weight="700" fill="url(#dcKnight)" stroke="#0a0a0a" stroke-width="1.75" paint-order="stroke">♞</text>
</svg>
