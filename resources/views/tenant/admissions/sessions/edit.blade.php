@extends('layouts.app')
@section('title', __('ui.nav.admission_sessions'))
@section('page-header')<div><h1 class="text-2xl font-semibold tracking-tight">{{ $session->name }}</h1></div>@endsection
@section('content')
<form method="POST" action="{{ route('tenant.admission-sessions.update', $session) }}" class="max-w-2xl">@csrf @method('PUT')
@include('tenant.admissions.sessions._form', ['session' => $session])
</form>
@endsection
