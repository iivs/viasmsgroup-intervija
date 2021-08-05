<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Virtual Wallet') }} - @yield('title')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
    <style>
        .form-group.required .control-label:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>

<div class="container my-5">
    @include('partials.navbar')

    @yield('content')
</div>

<!-- Hide success messages after some time. -->
<script>
    $('div.alert')
        .not('.alert-important, .alert-danger')
        .delay(5000)
        .fadeOut(350);
</script>
</body>
</html>