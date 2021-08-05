@extends('layouts.app')

@section('title') {{ __('My wallets') }} @endsection

@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success" role="alert">{{ Session::get('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="font-medium text-red-600">
                {{ __('Whoops! Something went wrong.') }}
            </div>
            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <a class="btn btn-success mb-3" href="{{ route('wallet.add') }}">+ {{ __('Add wallet') }}</a>

    <div class="table-responsive-md">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th scope="col">{{ __('ID') }}</th>
                    <th scope="col">{{ __('Name') }}</th>
                    <th scope="col">{{ __('Wallet address') }}</th>
                    <th scope="col">{{ __('Balance') }} €</th>
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
                        <td>
                            <a class="btn btn-link link-primary float-start border-end" style="border-radius: 0" href="{{ route('wallet.show', $wallet->id) }}">{{ __('View') }}</a>
                            <a class="btn btn-link link-primary float-start border-end" style="border-radius: 0" href="{{ route('wallet.edit', $wallet->id) }}">{{ __('Edit') }}</a>
                            <form action="{{ route('wallet.delete', $wallet->id) }}" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link link-primary float-start" onclick="return confirm('{{ __('Are you sure you want to delete this wallet?') }}')">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $wallets->links() }}
@endsection
