<!DOCTYPE html>
<html lang="{{ setting('localeCode', 'en') }}" dir="{{ isRTL() ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - {{ __('Description') }}</title>
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

        img {
            max-width: 100% !important;
            height: auto;
            display: block;
        }

        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
            max-width: 100% !important;
            margin: 20px 0;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100% !important;
            height: 100%;
        }

        .instagram-container,
        .tiktok-container {
            margin: 20px 0;
            width: 100% !important;
            overflow: hidden;
        }

        .instagram-container iframe {
            width: 100% !important;
            margin: 0 auto !important;
        }

        .media-link {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
            text-align: center;
        }

        .media-link a {
            color: #0066cc;
            text-decoration: none;
            font-weight: bold;
        }

        .media-link a:hover {
            text-decoration: underline;
        }

        #description-container {
            position: relative;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        #description-container.collapsed {
            max-height: 1000px;
        }

        #description-container.expanded {
            max-height: none;
        }

        .gradient-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 80px;
            background: linear-gradient(to bottom, transparent, rgba(255, 255, 255, 0.95));
            pointer-events: none;
        }

        #description-wrapper {
            position: relative;
        }

        #toggle-button {
            display: none;
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px 20px;
            background-color: white;
            color: black;
            border: 2px solid black;
            border-radius: 0;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            z-index: 10;
        }

        #toggle-button:hover {
            background-color: #f0f0f0;
        }

        #toggle-button.visible {
            display: block;
        }

        #toggle-button.expanded-state {
            position: relative;
            bottom: auto;
            left: auto;
            transform: none;
            margin: 10px auto;
            display: block;
        }
    </style>
    <!-- Add jQuery for DOM manipulation -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div id="description-wrapper">
        <div id="description-container" class="collapsed">
            {!! $product->description !!}
        </div>
        <div class="gradient-overlay" id="gradient-overlay"></div>
        <button id="toggle-button" data-text-more="{{ __('View More') }}" data-text-less="{{ __('Show Less') }}">
            {{ __('View More') }}
        </button>
    </div>
    <script>
        // Function to set custom button text from Flutter WebView
        function setToggleButtonText(moreText, lessText) {
            var button = document.getElementById('toggle-button');
            if (button) {
                button.setAttribute('data-text-more', moreText);
                button.setAttribute('data-text-less', lessText);

                // Update current button text based on current state
                var container = document.getElementById('description-container');
                if (container.classList.contains('collapsed')) {
                    button.textContent = moreText;
                } else {
                    button.textContent = lessText;
                }

                console.log("Button text updated - More:", moreText, "Less:", lessText);
            }
        }

        // Function to notify Flutter WebView about height changes
        function notifyHeightChange() {
            // Calculate actual visible height based on content
            var wrapper = document.getElementById('description-wrapper');
            var container = document.getElementById('description-container');
            var button = document.getElementById('toggle-button');

            // Get the actual rendered height of the wrapper (includes collapsed container + button)
            var height = wrapper.getBoundingClientRect().height;

            // If button is in expanded state, add its height separately
            if (button.classList.contains('expanded-state')) {
                height += button.getBoundingClientRect().height + 10; // 10px margin
            }

            // Add body padding/margin if any
            var bodyStyle = window.getComputedStyle(document.body);
            var bodyPaddingTop = parseFloat(bodyStyle.paddingTop) || 5;
            var bodyPaddingBottom = parseFloat(bodyStyle.paddingBottom) || 10;
            height += bodyPaddingTop + bodyPaddingBottom;

            // console.log("Actual visible height:", height);

            // Try different methods to communicate with Flutter WebView
            if (window.flutter_inappwebview) {
                window.flutter_inappwebview.callHandler('heightChanged', height);
            }
            if (window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.heightChanged) {
                window.webkit.messageHandlers.heightChanged.postMessage(height);
            }
            // Post message for general WebView communication
            window.parent.postMessage({
                type: 'heightChanged',
                height: height
            }, '*');
        }

        // Function to process oembed tags
        function processOembedTags() {

            // Try multiple selectors
            var oembedElements1 = $('figure.media oembed');
            var oembedElements2 = $('oembed');
            var oembedElements3 = document.getElementsByTagName('oembed');
            var oembedElements4 = document.querySelectorAll('oembed');

            // Use the one that finds elements, prefer vanilla JS
            var oembedElements = oembedElements4.length > 0 ? oembedElements4 :
                oembedElements3.length > 0 ? oembedElements3 :
                    oembedElements2;



            // Convert to array for iteration
            var elementsArray = Array.prototype.slice.call(oembedElements);

            elementsArray.forEach(function (element) {
                var url = element.getAttribute('url');
                if (!url) {
                    console.log("No URL found for oembed element");
                    return;
                }

                console.log("Processing oembed URL:", url);
                var embedHtml = '';

                // YouTube handling
                if (url.indexOf('youtube.com') !== -1 || url.indexOf('youtu.be') !== -1) {
                    var videoId = '';
                    if (url.indexOf('youtube.com') !== -1) {
                        videoId = url.split('v=')[1];
                        var ampersandPosition = videoId.indexOf('&');
                        if (ampersandPosition !== -1) {
                            videoId = videoId.substring(0, ampersandPosition);
                        }
                    } else if (url.indexOf('youtu.be') !== -1) {
                        videoId = url.split('/').pop();
                    }

                    if (videoId) {
                        // Use iframe with mobile-friendly parameters and thumbnail fallback
                        var thumbnailUrl = 'https://img.youtube.com/vi/' + videoId + '/hqdefault.jpg';
                        var watchUrl = 'https://www.youtube.com/watch?v=' + videoId;

                        embedHtml = '<div class="video-container" style="position: relative;">' +
                            '<iframe src="https://www.youtube.com/embed/' + videoId +
                            '?playsinline=1&rel=0&enablejsapi=1" ' +
                            'frameborder="0" ' +
                            'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" ' +
                            'allowfullscreen ' +
                            'referrerpolicy="strict-origin-when-cross-origin">' +
                            '</iframe>' +
                            '</div>';
                    }
                }
                // Vimeo handling
                else if (url.indexOf('vimeo.com') !== -1) {
                    var vimeoId = url.split('/').pop();
                    embedHtml = '<div class="video-container">' +
                        '<iframe src="https://player.vimeo.com/video/' + vimeoId + '" ' +
                        'frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>' +
                        '</div>';
                }
                // Dailymotion handling
                else if (url.indexOf('dailymotion.com') !== -1 || url.indexOf('dai.ly') !== -1) {
                    var dailymotionId = '';
                    if (url.indexOf('dailymotion.com') !== -1) {
                        dailymotionId = url.split('/video/')[1];
                        if (dailymotionId && dailymotionId.indexOf('?') !== -1) {
                            dailymotionId = dailymotionId.substring(0, dailymotionId.indexOf('?'));
                        }
                    } else if (url.indexOf('dai.ly') !== -1) {
                        dailymotionId = url.split('/').pop();
                    }

                    if (dailymotionId) {
                        embedHtml = '<div class="video-container">' +
                            '<iframe src="https://www.dailymotion.com/embed/video/' + dailymotionId + '" ' +
                            'frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>' +
                            '</div>';
                    }
                }
                // For other videos, use HTML5 video tag if it's a common video format
                else {
                    var fileExtension = url.split('.').pop().toLowerCase();
                    var videoFormats = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];

                    if (videoFormats.indexOf(fileExtension) !== -1) {
                        embedHtml = '<div class="video-container">' +
                            '<video width="100%" height="100%" controls>' +
                            '<source src="' + url + '" type="video/' +
                            (fileExtension === 'mov' ? 'quicktime' :
                                fileExtension === 'm4v' ? 'mp4' : fileExtension) + '">' +
                            'Your browser does not support the video tag.' +
                            '</video>' +
                            '</div>';
                    } else {
                        // For other links, create a simple link
                        var title = $(this).attr('title') || 'View Video';
                        embedHtml = '<div class="media-link"><a href="' + url +
                            '" target="_blank" rel="noopener">' + title + '</a></div>';
                    }
                }

                // Replace the oembed tag with our generated HTML
                if (embedHtml) {
                    // Try to find parent figure element
                    var parent = element.parentElement;
                    if (parent && parent.tagName === 'FIGURE' && parent.classList.contains('media')) {
                        var tempDiv = document.createElement('div');
                        tempDiv.innerHTML = embedHtml;
                        parent.parentNode.replaceChild(tempDiv.firstChild, parent);
                        console.log("Replaced oembed with embed HTML (via parent figure)");
                    } else {
                        // Just replace the oembed element itself
                        var tempDiv = document.createElement('div');
                        tempDiv.innerHTML = embedHtml;
                        element.parentNode.replaceChild(tempDiv.firstChild, element);
                        console.log("Replaced oembed with embed HTML (direct)");
                    }
                }
            });
        }

        $(document).ready(function () {
            // Process oembed tags immediately
            processOembedTags();

            // Check if content height exceeds 1000px and show/hide toggle button
            var container = $('#description-container');
            var toggleButton = $('#toggle-button');
            var gradientOverlay = $('#gradient-overlay');
            var wrapper = $('#description-wrapper');
            var maxHeight = 1000; // 1000px max height

            // Get the actual content height using same method as notifyHeightChange
            container.removeClass('collapsed').addClass('expanded');
            var contentHeight = wrapper[0].getBoundingClientRect().height;

            // If content is taller than 1000px, show the toggle button
            if (contentHeight > maxHeight) {
                container.removeClass('expanded').addClass('collapsed');
                toggleButton.addClass('visible');
                gradientOverlay.show();

                // Toggle button click handler
                toggleButton.on('click', function () {
                    // Always read current text from data attributes
                    var textMore = toggleButton.data('text-more');
                    var textLess = toggleButton.data('text-less');

                    if (container.hasClass('collapsed')) {
                        container.removeClass('collapsed').addClass('expanded');
                        toggleButton.text(textLess);
                        toggleButton.addClass('expanded-state');
                        gradientOverlay.hide();

                        // Notify height change after transition
                        setTimeout(function () {
                            notifyHeightChange();
                        }, 350);
                    } else {
                        container.removeClass('expanded').addClass('collapsed');
                        toggleButton.text(textMore);
                        toggleButton.removeClass('expanded-state');
                        gradientOverlay.show();

                        // Notify height change after transition
                        setTimeout(function () {
                            notifyHeightChange();
                        }, 350);

                        // Scroll to top of description when collapsing
                        $('html, body').animate({
                            scrollTop: $('#description-wrapper').offset().top - 20
                        }, 300);
                    }
                });
            } else {
                // Content fits within 40vh, keep it expanded and hide overlay
                gradientOverlay.hide();
            }

            // Notify initial height after page load
            setTimeout(function () {
                notifyHeightChange();
            }, 500);
        });
    </script>
</body>

</html>