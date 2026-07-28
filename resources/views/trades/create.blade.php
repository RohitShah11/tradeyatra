@extends('layouts.app')

@section('page_title', 'Add Trade')
@section('page_subtitle', 'Log the setup, execution, psychology, risk, result, and screenshots.')

@section('content')
<form method="POST" action="{{ route('trades.store') }}" enctype="multipart/form-data">
    @include('trades._form')
</form>
@endsection
