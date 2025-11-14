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
        .photo-carousel {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
    }

    /* Окошко, через которое смотрим на ленту */
    .photos-viewport {
        overflow: hidden;
        width: 100%;
    }

    /* Лента со слайдами */
    .photos-strip {
        display: flex;
        transition: transform 0.3s ease;
    }

    /* Один слайд = ширина окна */
    .house-photo {
        flex: 0 0 100%;
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    .photo-nav {
        flex: 0 0 auto;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 1px solid #ccc;
        background: #f5f5f7;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
        padding: 0;
        transition: background 0.2s, transform 0.1s;
    }

    .photo-nav:hover {
        background: #e0e0ee;
        transform: scale(1.05);
    }

    .no-photos {
        font-size: 14px;
        color: #777;
    }
        


        
    </style>
</head>
<body>
    <div>
        @yield('main_content')
    </div>
</body>
</html>