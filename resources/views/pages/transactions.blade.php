@extends('layouts.app')

@section('title') {{ __('Transactions') }} @endsection

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

    <a class="btn btn-success mb-3" href="{{ route('transaction.add', ['id' => (Route::input('id') === null || Route::input('id') == 0) ? 0 : $wallets[0]->id ?? 0]) }}">+ {{ __('Add transaction') }}</a>
    <br />

    <h3 class="float-start me-3">Select a wallet:</h3>
    <select class="float-start mt-1" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
        <option value="{{ route('transactions.one', ['id' => 0, 'param' => Route::input('param')]) }}" @if (Route::input('id') === null || Route::input('id') === 0) selected="selected" @endif>{{ __('All') }}</option>
        @foreach ($wallets_all as $wallet_all)
            <option value="{{ route('transactions.one', ['id' => $wallet_all->id, 'param' => Route::input('param')]) }}" @if (Route::input('id') == $wallet_all->id) selected="selected" @endif>{{ $wallet_all->name }}</option>
        @endforeach
    </select>
    <br clear="left" />

    <h3 class="float-start me-3">Transactions:</h3>
    <select class="float-start mt-1" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
        <option value="{{ route('transactions.one', ['id' => (Route::input('id') === null || Route::input('id') == 0) ? 0 : $wallets[0]->id ?? 0, 'param' => null]) }}" @if (Route::input('param') === null) selected="selected" @endif>{{ __('All') }}</option>
        <option value="{{ route('transactions.one', ['id' => (Route::input('id') === null || Route::input('id') == 0) ? 0 : $wallets[0]->id ?? 0, 'param' => 'in']) }}" @if (Route::input('param') === 'in') selected="selected" @endif>{{ __('Incoming') }}</option>
        <option value="{{ route('transactions.one', ['id' => (Route::input('id') === null || Route::input('id') == 0) ? 0 : $wallets[0]->id ?? 0, 'param' => 'out']) }}" @if (Route::input('param') === 'out') selected="selected" @endif>{{ __('Outgoing') }}</option>
    </select>
    <br clear="left" />

    <div class="table-responsive-md">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th scope="col">{{ __('ID') }}</th>
                    <th scope="col">{{ __('From') }}</th>
                    <th scope="col">{{ __('To') }}</th>
                    <th scope="col">{{ __('Amount') }} €</th>
                    <th scope="col">{{ __('Fraudulent') }}</th>
                    <th scope="col">{{ __('Date') }}</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($all_transactions as $transaction)
                    <tr>
                        <th scope="row">{{ $transaction['id'] }}</th>
                        <td style="width: 225px">{{ $transaction['from'] }}</td>
                        <td style="width: 225px">{{ $transaction['to'] }}</td>
                        <td>{{ $transaction['amount'] }}</td>
                        <td style="width: 150px">{{ $transaction['is_fraudulent'] }}</td>
                        <td style="width: 255px">{{ $transaction['created_at'] }}</td>
                        <td>
                            <form action="{{ route('transaction.update', $transaction['id']) }}" method="post">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-link link-primary float-start" onclick="return confirm('{{ __('Change transaction status?') }}')">{{ __('Fraudulent') }}</button>
                            </form>
                            <form action="{{ route('transaction.delete', $transaction['id']) }}" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link link-primary float-start" onclick="return confirm('{{ __('Are you sure you want to delete this transaction?') }}')">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <td colspan="3" class="text-end">
                    @if (Route::input('param') === null)
                        <span class="text-success">{{ __('Incoming') }}:</span><br />
                        <span class="text-danger">{{ __('Outgoing') }}:</span><br />
                    @endif
                    <span class="{{ $totals['style'] }}"><strong>{{ __('Total') }}:</strong></span>
                </td>
                <td colspan="4">
                    @if (Route::input('param') === null) 
                        <span class="text-success">{{ $totals['in'] }} €</span><br />
                        <span class="text-danger">{{ $totals['out'] }} €</span><br />
                    @endif
                    <span class="{{ $totals['style'] }}"><strong>{{ $totals['total'] }} €</strong></span>
                </td>
            </tfoot>
        </table>
    </div>
@endsection
