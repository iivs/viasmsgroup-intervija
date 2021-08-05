@extends('layouts.app')

@section('title') {{ __('My wallets') }} @endsection

@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success" role="alert">{{ Session::get('success') }}</div>
    @endif

    <div class="table-responsive-md">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th scope="col">{{ __('ID') }}</th>
                    <th scope="col">{{ __('Name') }}</th>
                    <th scope="col">{{ __('Wallet address') }}</th>
                    <th scope="col">{{ __('Balance') }} €</th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($wallets as $wallet)
                    <tr>
                        <th scope="row">{{ $wallet->id }}</th>
                        <td>{{ $wallet->name }}</td>
                        <td>{{ $wallet->uniqid }}</td>
                        <td>{{ $wallet->balance }}</td>
                        <td><a href="#">{{ __('View') }}</a></td>
                        <td><a href="{{ route('wallet.edit', $wallet) }}">{{ __('Edit') }}</a></td>
                        <td><a href="#">{{ __('Delete') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $wallets->links() }}
@endsection
