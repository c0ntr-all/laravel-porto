<x-mail::message>
# {{ $subject }}

{{ $body }}

@if(!empty($meta['task_title']))
**Task:** {{ $meta['task_title'] }}
@endif

@if(!empty($meta['event_at']))
**Event time:** {{ $meta['event_at'] }}
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
