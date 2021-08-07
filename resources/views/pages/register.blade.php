@extends('layouts.app')

@section('title') {{ __('Register') }} @endsection

@section('content')
    <div class="row">
        <div class="col-md-4 offset-md-4">
            <div class="card">
                <div class="card-header">
                    {{ __('Register') }}
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

                    <form action="{{ route('user.register') }}" method="post">
                        @csrf

                        <div class="mb-3 form-group required">
                            <label for="name" class="form-label control-label">{{ __('Name') }}:</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
                        </div>
                        <div class="mb-3 form-group required">
                            <label for="email" class="form-label control-label">{{ __('E-mail') }}:</label>
                            <input type="text" class="form-control" id="email" name="email" value="{{ old('email') }}">
                        </div>
                        <div class="mb-3 form-group required">
                            <label for="password" class="form-label control-label">{{ __('Password') }}:</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <div class="mb-3 form-group required">
                            <label for="password_confirmation" class="form-label control-label">{{ __('Confirm password') }}:</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('Register') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection