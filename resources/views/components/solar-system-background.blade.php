{{-- Signature 3D solar-system background (§3): fixed WebGL canvas behind all
     content (z-0, pointer-events-none) + static cosmic fallback if WebGL is
     unavailable. Decorative only: aria-hidden. One instance per page. --}}
<canvas id="solar-system-canvas" class="pointer-events-none" aria-hidden="true" data-solar-system></canvas>
<div class="solar-fallback-bg" style="display: none;" aria-hidden="true"></div>
