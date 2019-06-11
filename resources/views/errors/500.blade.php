@extends('errors::layout')

@section('title', 'Error')

@section('message')

Whoops, looks like something went wrong.

@if(app()->bound('sentry') && app('sentry')->getLastEventId())
    <div class="subtitle">Error ID: <b>{{ app('sentry')->getLastEventID() }}</b></div>
    <script src="https://browser.sentry-cdn.com/5.3.0/bundle.min.js" crossorigin="anonymous"></script>
    <script>
        Sentry.init({ dsn: '{{ env('SENTRY_LARAVEL_DSN') }}' });
        Sentry.showReportDialog({
            eventId: '{{ app('sentry')->getLastEventID() }}',
            // use the public DSN (dont include your secret!)
            dsn: '{{ env('SENTRY_LARAVEL_DSN') }}',
            user: {
                'name': '{{ \Auth::user()->name ?? '' }}',
                'email': '{{ \Auth::user()->email ?? '' }}',
            }
        });
    </script>
@endif

@endsection