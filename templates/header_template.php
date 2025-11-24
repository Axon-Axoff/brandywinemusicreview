<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $site_title ?? 'Music DB' ?></title>

  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous">

  <!-- Brandywine custom styles -->
  <link rel="stylesheet" href="/css/styles.css">
  <script>
    function safeBack(fallbackUrl) {
      if (document.referrer) {
        if (document.referrer.startsWith(window.location.origin)) {
          history.back();
          return;
        }
      }
      window.location.href = fallbackUrl;
    }
  </script>
</head>

<body>