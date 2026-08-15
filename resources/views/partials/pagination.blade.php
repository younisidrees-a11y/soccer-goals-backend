@if ($paginator->hasPages())
<div style="display:flex;justify-content:center;align-items:center;gap:14px;margin-top:28px;">
  @if ($paginator->onFirstPage())
    <span class="btn btn-ghost" style="opacity:.4;pointer-events:none;">Previous</span>
  @else
    <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-ghost">Previous</a>
  @endif

  <span style="font-size:13.5px;color:var(--ink-faint);">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

  @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-ghost">Next</a>
  @else
    <span class="btn btn-ghost" style="opacity:.4;pointer-events:none;">Next</span>
  @endif
</div>
@endif
