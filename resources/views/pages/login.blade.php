@extends('layouts.app')

@section('title') {{ __('Login') }} @endsection

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

    <form action="{{ route('user.login') }}" method="post">
        @csrf

        <div class="row">
            <div class="col">
                <input type="text" class="form-control" placeholder="E-mail" aria-label="email" name="email" value="{{ old('email') }}">
            </div>
            <div class="col">
                <input type="password" class="form-control" placeholder="Password" aria-label="password" name="password">
            </div>
            <div class="col">
                <button type="submit" class="btn btn-primary">{{ __('Log in') }}</button>
            </div>
        </div>
    </form>
@endsection