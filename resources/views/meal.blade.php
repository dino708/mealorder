<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Meal Order · ZeroBug</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="mo">
@php($isAdmin = auth()->user()?->role === 'admin')
<main id="meal-app" data-role="{{ auth()->user()?->role ?? 'guest' }}" class="{{ $isAdmin ? 'mo-admin' : 'mo-shop' }}">
    @if($isAdmin)
        @include('meal.admin')
    @else
        @include('meal.customer')
    @endif
</main>
</body>
</html>
