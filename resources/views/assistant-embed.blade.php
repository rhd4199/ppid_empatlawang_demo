{{-- /sapa-madani/embed: the Sapa Madani chat panel alone, filling the iframe that embed.js opens on other sites. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Sapa Madani — PPID Kabupaten Empat Lawang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; background: #fff; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        button, input { font: inherit; }
        button { cursor: pointer; }
        .sm-logo { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        /* the frame is the panel: no floating position, size, shadow or open animation */
        body.sm-embed .sm-panel {
            position: static; inset: auto; width: 100%; max-width: none; height: 100vh; height: 100dvh; max-height: none;
            border: 0; border-radius: 0; box-shadow: none; opacity: 1; transform: none; transition: none;
        }
    </style>
</head>
<body class="sm-embed">
    @include('partials.assistant-panel', ['embed' => true])
</body>
</html>
