<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1">

  <title>ChatApp</title>

  <!-- Fonts -->
  <link
    href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap"
    rel="stylesheet">
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
  <!-- Styles -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6"
    crossorigin="anonymous">
  <link
    rel="stylesheet"
    href="/css/app.css">

  <style>
  body {
      font-family: 'Nunito', sans-serif;
  }
  </style>
</head>
<body>
<div
  class="container"
  id="app">
  <pin-reset></pin-reset>
</div>

<script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
