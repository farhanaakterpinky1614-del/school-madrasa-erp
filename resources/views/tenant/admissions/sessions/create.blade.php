@extends('layouts.app')
@section('title', __('ui.nav.admission_sessions'))
@section('page-header')<div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_admission_session') }}</h1></div>@endsection
@section('content')
<form method="POST" action="{{ route('tenant.admission-sessions.store') }}" class="max-w-2xl">@csrf
@include('tenant.admissions.sessions._form')
</form>
@endsection
