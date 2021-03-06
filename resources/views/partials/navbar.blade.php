<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            @if (Route::has('login'))
                @auth
                    <li class="nav-item">
                        <a class="nav-link @if (Route::is('wallet.list')) active @endif" href="{{ url('/') }}">{{ __('My Wallets') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (Route::is('transactions.all') || Route::is('transactions.one') || Route::is('transaction.add')) active @endif" href="{{ url('/transactions') }}">{{ __('Transactions') }}</a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a class="nav-link" style="cursor: pointer" :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log out') }}</a>
                        </form>
                    </li>
                @else
                    <li class="nav-item @if (Route::is('login')) active @endif">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item @if (Route::is('register')) active @endif">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @endauth
            @endif
        </ul>
    </div>
</nav>
<hr>