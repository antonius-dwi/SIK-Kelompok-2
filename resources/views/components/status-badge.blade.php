@props(['status'])
<span class="badge status-badge {{ $status->cssClass() }}"><span></span>{{ $status->label() }}</span>
