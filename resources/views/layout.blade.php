<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        

        * {
            box-sizing: border-box;
        }
        
        .element {
            --index: calc(1vw * 1vh);
        }

        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            overflow: hidden;
        }
        #map {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
        }
        .overlay {
            position: relative;
            z-index: 1;
            display: flex;
            height: 100vh;
            width: 100vw;
            pointer-events: none;
        }
        .panel {
            pointer-events: auto;
            display: flex;
            flex-direction: column;
            background: #f7f7f9;
            border-right: 1px solid #ddd;
            padding: 12px;
            overflow-y: auto;
            /* width: calc(var(--index) * 10); */
            width: 280px;
        }
        #left-panel {
            background: #f7f7f9;
        }
        #middle-panel {
            background: #ffffff;
        }

        #right-panel {
            background: #f7f7f9;
            border-right: none;
            border-left: 1px solid #ddd;
        }
        .map-spacer {
            flex: 1;
            pointer-events: none;
        }
        .panel-header {
            align-items: center;
            justify-content: space-between;
            min-height: 36px;
            margin-bottom: 8px;
        }
        .panel-title {
            font-size: 16px;
            font-weight: 600;
            white-space: nowrap;
        }
        .panel-body {
            margin-top: 4px;
        }

        .panel.collapsed {
            width: 40px;
            min-width: 40px;
        }

        .panel.collapsed .panel-title,
        .panel.collapsed .panel-body {
            display: none;
        }
    </style>
</head>
<body>
    <div>
        @yield('main_content')
    </div>
</body>
</html>