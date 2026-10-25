<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $vendor->name }} - {{ __('Description') }}</title>
    <style>
        /* body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        } */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
            max-width: 100%;
            margin: 20px 0;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    </style>
    <!-- Add jQuery for DOM manipulation -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div>{!! $vendor->description !!}</div>

    <script src="{{ asset('js/model_description_handler.js') }}"></script>
</body>

</html>