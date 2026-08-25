{{-- Small jersey icon for lineup cards. Expects $color (hex), $number, $textColor. --}}
<svg class="jersey-svg" viewBox="0 0 52 56" role="img" aria-label="Shirt number {{ $number }}">
  <path
    d="M18,4 L8,10 L2,20 L10,26 L14,22 L14,52 L38,52 L38,22 L42,26 L50,20 L44,10 L34,4 L30,9 Q26,13 22,9 Z"
    fill="{{ $color }}" stroke="rgba(127,127,127,.45)" stroke-width="1" stroke-linejoin="round" />
  <text x="26" y="38" text-anchor="middle" font-family="var(--font-display)" font-size="17" font-weight="700" fill="{{ $textColor }}">{{ $number }}</text>
</svg>
