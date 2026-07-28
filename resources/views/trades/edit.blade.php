@extends('layouts.app')

@section('page_title', 'Edit Trade')
@section('page_subtitle', 'Tighten the record while the lesson is still clear.')

@section('content')
<form method="POST" action="{{ route('trades.update', $trade) }}" enctype="multipart/form-data">
    @include('trades._form')
</form>
@endsection
