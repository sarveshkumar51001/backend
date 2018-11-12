@if(empty($breadcrumb))
    <li class="breadcrumb-item"><i class="icon-home icons font-xl d-block"></i></li>
@else
    <li class="breadcrumb-item"><a href="/admin"><i class="icon-home icons font-xl d-block"></i></a></li>
    @foreach($breadcrumb as $title => $link)
        <li class="breadcrumb-item">
            @if(!$loop->last && !empty($link))
                <a href="{{ $link }}">{{ $title }}</a>
            @else
                {{ $title }}
            @endif
        </li>
    @endforeach
@endif
<!-- Breadcrumb Menu-->


