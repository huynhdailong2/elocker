<!DOCTYPE html>
<html lang="{{ $userLocale }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="masterdata-version" content="{{ $dataVersion }}">
    <title>{{ config('app.name', 'WebApp') }}</title>
    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed&display=swap" rel="stylesheet">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    {{-- <link href="{{ mix('css/styles.css') }}" rel="stylesheet"> --}}
    <link rel="icon" href="/images/favicon.ico">
</head>
<body>
    <div id="app">
    </div>

    <!-- Scripts -->
    <script>
        const API_URL = "{{ env('API_URL', 'http://' . Request::getHost()) }}";
    </script>
    <script src="{{ mix('js/manifest.js')}}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
    <script type="text/javascript">
        function requestFullScreen(element) {
            // Supports most browsers and their versions.
            const requestMethod = element.requestFullScreen || element.webkitRequestFullScreen
                || element.mozRequestFullScreen || element.msRequestFullScreen;

            // Native full screen.
            if (requestMethod) {
                console.log('dasdasdasdasd:: ', requestMethod)
                requestMethod.call(element);
            }
            // Older IE.
            else if (typeof window.ActiveXObject !== "undefined") {
                const wscript = new ActiveXObject("WScript.Shell");
                if (wscript !== null) {
                    console.log('dasdasdasdasd:: ', wscript)
                    wscript.SendKeys("{F11}");
                }
            }
        }

        // Make the body go full screen.
        const elem = document.body;
        // requestFullScreen(elem);
    </script>
</body>
</html>
