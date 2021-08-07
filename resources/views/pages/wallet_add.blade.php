@extends('layouts.app')

@section('title') {{ __('Add wallet') }} @endsection

@section('content')
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

    <form action="{{ route('wallet.store') }}" method="post">
        @csrf
        <div class="mb-3 form-group required">
            <label for="name" class="form-label control-label">{{ __('Name') }}:</label>
            <input type="text" class="form-control w-25" id="name" name="name" value="{{ old('name') }}" maxlength="255">
        </div>
        <button type="submit" class="float-start me-3 btn btn-primary">{{ __('Save') }}</button>
    </form>
    <a href="{{ url()->previous() }}" class="float-start btn btn-primary">{{ __('Cancel') }}</a>
@endsection
