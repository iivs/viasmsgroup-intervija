@extends('layouts.app')

@section('title') {{ __('Login') }} @endsection

@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success" role="alert">{{ Session::get('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-4 offset-md-4">
            <div class="card">
                <div class="card-header">
                    {{ __('Login') }}
                </div>

                <div class="card-body">
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

                        <div class="mb-3 form-group">
                            <label for="email" class="form-label control-label">{{ __('E-mail') }}:</label>
                            <input  type="text" class="form-control" id="email" name="email" value="{{ old('email') }}">
                        </div>
                        <div class="mb-3 form-group">
                            <label for="password" class="form-label control-label">{{ __('Password') }}:</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('Log in') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection