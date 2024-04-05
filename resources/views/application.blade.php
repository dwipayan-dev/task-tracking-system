<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="https://quocent.com/wp-content/uploads/2023/07/favicon-150x150.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Task Tracking System</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('loader.css') }}" />
    @vite(['resources/js/main.js'])
</head>

<body>
    <div id="app">
        <div id="loading-bg">
            <div class="loading-logo">
                <!-- SVG Logo -->
                <img class="d-flex" style="height:100px;" src="/logo.png" />
            </div>
            <div class="loading">
                <div class="effect-1 effects"></div>
                <div class="effect-2 effects"></div>
                <div class="effect-3 effects"></div>
            </div>
        </div>
    </div>

    <script>
        var global = window;
        const loaderColor = localStorage.getItem('sneat-initial-loader-bg') || '#FFFFFF'
        const primaryColor = localStorage.getItem('sneat-initial-loader-color') || 'rgb(186, 0, 0)'

        if (loaderColor)
            document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)

        if (primaryColor)
            document.documentElement.style.setProperty('--initial-loader-color', primaryColor)
    </script>
</body>

</html>
