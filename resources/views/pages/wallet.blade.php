@extends('layouts.app')

@section('title') {{ $wallet->name }} @endsection

@section('content')
    <ul class="list-group list-group-flush">
        <li class="list-group-item"><h1>Wallet: "{{ $wallet->name }}"</h1></li>
        <li class="list-group-item"><h3 class="float-start me-3">Address:</h3><input class="w-25 form-control float-start" type="text" value="{{ $wallet->uniqid }}" aria-label="readonly input example" readonly /></li>
        <li class="list-group-item"><h3>Last edited: {{ $wallet->updated_at }}</h3></li>
        <li class="list-group-item"><h3>Balance: <strong>{{ $wallet->balance }} €</strong></h3></li>
    </ul>

    <a href="{{ route('wallet.edit', $wallet->id) }}" class="float-start me-3 btn btn-primary">{{ __('Edit') }}</a>

    <form action="{{ route('wallet.delete', $wallet->id) }}" method="post">
        @csrf
        @method('DELETE')
        <button type="submit" class="float-start me-3 btn btn-primary" onclick="return confirm('{{ __('Are you sure you want to delete this wallet?') }}')">{{ __('Delete') }}</button>
    </form>

    <a href="{{ route('wallet.list') }}" class="float-start btn btn-primary">{{ __('Back') }}</a>
@endsection
