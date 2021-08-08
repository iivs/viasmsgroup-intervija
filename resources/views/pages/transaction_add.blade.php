@extends('layouts.app')

@section('title') {{ __('Add transaction') }} @endsection

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

    <form action="{{ route('transaction.store') }}" method="post">
        @csrf

        <div class="row">
            <div class="col">
                <div class="mb-3 form-group">
                    <label for="type_from" class="form-label control-label">{{ __('From') }}:</label>
                    <select name="type_from" id="type_from">
                        @foreach (\Config::get('transactions.type') as $type_from)
                            <option value="{{ $type_from }}" @if (old('type_from') == $type_from) selected="selected" @endif>
                                @php
                                    switch ($type_from) {
                                        case \Config::get('transactions.type.wallet'):
                                            echo __('Wallet');
                                            break;
                                        case \Config::get('transactions.type.card'):
                                            echo __('Credit card');
                                            break;
                                    }
                                @endphp
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="wallet_from_container" style="display: none;">
                    <div class="mb-3 form-group">
                        <label for="wallet_from" class="form-label control-label float-start me-1">{{ __('Wallet') }}:</label>
                        <select name="wallet_from" id="wallet_from" class="float-start me-3">
                            @foreach ($wallets_all as $wallet_all)
                                <option value="{{ $wallet_all->id }}" data-balance="{{ $wallet_all->balance }}" data-address="{{ $wallet_all->uniqid }}" @if (Route::input('id') == $wallet_all->id) selected="selected" @endif>{{ $wallet_all->name }}</option>
                            @endforeach
                        </select>
                        <input class="w-50 form-control float-start me-3" id="address" type="text" value="" aria-label="readonly input example" readonly />
                        <strong class="float-start"><span id="balance"></span> €</strong><br clear="right">
                    </div>
                </div>

                <div id="card_from_container" style="display: none;">
                    <div class="mb-3 form-group required">
                        <label for="holder_from" class="form-label control-label">{{ __('Card holder name') }}:</label>
                        <input class="form-control w-50" id="holder" name="holder_from" value="{{ old('holder_from') }}" maxlength="255">
                    </div>
                    <div class="mb-3 form-group required">
                        <label for="card_from" class="form-label control-label">{{ __('Card number') }}:</label>
                        <input class="form-control w-50" id="card_from" name="card_from" value="{{ old('card_from') }}" placeholder="0000-0000-0000-0000" maxlength="255">
                    </div>
                    <div class="mb-3 form-group required">
                        <label for="date_m" class="form-label control-label">{{ __('Expiry date') }}:</label><br clear="right" />
                        <input class="form-control float-start me-1" style="width: 55px;" id="date_m" name="date_m" value="{{ old('date_m') }}" placeholder="mm" maxlength="2">
                        <span class="float-start me-1">/</span>
                        <input class="form-control float-start" style="width: 55px;" id="date_y" name="date_y" value="{{ old('date_y') }}" placeholder="yy" maxlength="2"><br />
                    </div>
                    <div class="mb-3 form-group required">
                        <label for="cvc" class="form-label control-label">{{ __('CVC') }}:</label>
                        <input type="password" class="form-control" style="width: 60px;" id="cvc" name="cvc" maxlength="3">
                    </div>
                </div>

                <div class="mb-3 form-group required">
                    <label for="amount" class="form-label control-label">{{ __('Amount') }}:</label>
                    <input class="form-control w-25" id="amount" name="amount" value="{{ old('amount') }}" maxlength="255">
                </div>
            </div>

            <div class="col">
                <div class="mb-3 form-group">
                    <label for="type_to" class="form-label control-label">{{ __('To') }}:</label>
                    <select name="type_to" id="type_to"></select>
                </div>
                <div id="wallet_to_container" style="display: none;">
                    <div class="mb-3 form-group required">
                        <label for="wallet_to" class="form-label control-label">{{ __('Address') }}:</label>
                        <input class="form-control w-50" id="wallet_to" name="wallet_to" value="{{ old('wallet_to') }}" maxlength="255">
                    </div>
                </div>
                <div id="card_to_container" style="display: none;">
                    <div class="mb-3 form-group required">
                        <label for="holder_to" class="form-label control-label">{{ __('Card holder name') }}:</label>
                        <input class="form-control w-50" id="holder_to" name="holder_to" value="{{ old('holder_to') }}" maxlength="255">
                    </div>
                    <div class="mb-3 form-group required">
                        <label for="card_to" class="form-label control-label">{{ __('Card number') }}:</label>
                        <input class="form-control w-50" id="card_to" name="card_to" value="{{ old('card_to') }}" placeholder="0000-0000-0000-0000" maxlength="255">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="float-start me-3 btn btn-primary">{{ __('Add') }}</button>
    </form>
    <a href="{{ url()->previous() }}" class="float-start btn btn-primary">{{ __('Cancel') }}</a>

    <script>
        $("#type_from").change(function() {
            // Clear previous options.
            $('#type_to option').each(function() {
                $(this).remove();
            });

            // Populate the second select fields.
            $('#type_to')
                .append('<option value="{{ \Config::get('transactions.type.wallet') }}" @if (old('type_to') == \Config::get('transactions.type.wallet')) selected="selected" @endif>{{ __('Wallet') }}</option>');
                
            // Transactions from credit cards to credit cards are not allowed.
            if ($(this).val() == {{ \Config::get('transactions.type.wallet') }}) {
                $('#type_to')
                    .append('<option value="{{ \Config::get('transactions.type.card') }}" @if (old('type_to') == \Config::get('transactions.type.card')) selected="selected" @endif>{{ __('Credit card') }}</option>');

                // Show sender wallet form and hide credit card form.
                $('#wallet_from_container').show();
                $('#card_from_container').hide();
            } else {
                // Show sender credit card form and hide wallet form.
                $('#card_from_container').show();
                $('#wallet_from_container').hide();
            }

            $("#type_to").change();
        });

        $("#type_from").change();

        $("#type_to").change(function() {
            if ($(this).val() == {{ \Config::get('transactions.type.wallet') }}) {
                $('#wallet_to_container').show();
                $('#card_to_container').hide();
            } else {
                $('#card_to_container').show();
                $('#wallet_to_container').hide();
            }
        });

        $("#type_to").change();

        $("#wallet_from").change(function() {
           $('#address').val($(this).find(':selected').data('address'));
           $('#balance').text($(this).find(':selected').data('balance'));
        });

        $("#wallet_from").change();
    </script>
@endsection
