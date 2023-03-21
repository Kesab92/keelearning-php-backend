<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <link rel="stylesheet" href="/build/semantic.min.css">
    <title>
        keelearning - oops!
    </title>

    <link rel="apple-touch-icon" sizes="76x76" href="/meta/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/meta/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/meta/favicon-16x16.png">
    <link rel="manifest" href="/meta/site.webmanifest">
    <link rel="mask-icon" href="/meta/safari-pinned-tab.svg" color="#05bee6">
    <link rel="shortcut icon" href="/meta/favicon.ico">
    <meta name="msapplication-TileColor" content="#05bee6">
    <meta name="msapplication-config" content="/meta/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

    <style>
        .error-wrapper,
        .grid {
            height: 100%;
        }
        .column {
            padding: 2rem !important;
            position: relative;
        }
        #ghost {
            height: auto;
            image-rendering: pixelated;
            position: absolute;
            right: 16px;
            top: 16px;
            transform: scaleX(-1);
            width: 30px;
        }
        #body {
            fill: #fe0000;
            transition: fill 0.5s ease;
        }
        #eyes {
            fill: #f3f3ff;
        }
        #pupils {
            animation: blink 4s infinite;
            height: auto;
            image-rendering: pixelated;
            position: absolute;
            right: 16px;
            top: 16px;
            transform: scaleX(-1);
            transform-origin: center center;
            width: 30px;
            z-index: 2;
        }
        #pupils-path {
            transform-origin: center center;
            fill: #2121fe;
        }

        @keyframes blink {
            97% {
                animation-timing-function: ease-in;
                transform: scaleX(-1) scaleY(1);
            }

            98.5% {
                transform: scaleX(-1) scaleY(0);
            }

            100% {
                animation-timing-function: ease-out;
                transform: scaleX(-1) scaleY(1);
            }
        }
    </style>
</head>
<body>
<div class="error-wrapper">
    <div class="ui middle aligned one column centered grid">
        <div class="row">
            <div class="ui column piled text container segment">
                <div class="column">
                    <h3 class="ui header">
                        Oops!
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" id="ghost" width="50px" height="50px">
                            <path id="body" d="M476.17 220v37.17H440v35.6h-72.82v-35.45h-36.81V147.27h36.42v-36.19h73.16v35.84h35.99v-35.98h-35.96V74.23h-36.77V38.2h-73.19V1.02h-146v36.81h-72.81v36.02H74.18v37.03h-36v109.14H1.03v293h37v-36.81h35.82v-35.89h37.01v35.54h36.17v37.16h73v-72.7h73v72.7h74v-36.81h35.82v-35.89h37.01v35.54h36.17v37.16h37v-293c-12.31-.02-24.59-.02-36.86-.02zm-219.34-36v73.17H220v35.59h-72.98v-35.6h-35.64V147.31h35.35v-35.97h73.05v35.46h37.06c-.01 12.7-.01 24.95-.01 37.2z"/>
                            <path id="eyes" d="M220 257.17v35.59h-72.98v-35.6h-35.64V147.31h35.35v-35.97h73.05v35.46h37.06v37.19h-72.5v73.17c12.1.01 23.88.01 35.66.01zm255.93-110.26c0 12.4.01 24.81.01 37.21h-72.61v73.04H440v35.6h-72.82v-35.45h-36.81V147.26h36.42v-36.19h73.18l-.02-.14v35.98h35.98z"/>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" id="pupils" width="50px" height="50px">
                            <path id="pupils-path" d="M220 257.17h-35.67V184h72.5v73.17H220zm220 0h-36.67v-73.04h72.61c.08 11.96.15 23.92.23 35.88v37.17c-12.33-.01-24.25-.01-36.17-.01z"/>
                        </svg>
                    </h3>
                    <strong>
                        Ein Fehler ist aufgetreten.
                    </strong>
                    <p>
                        Die gute Nachricht ist: unsere Technik-Spezialisten sind bereits automatisch informiert worden.<br>
                        Ihr Fehler wird unter der ID <strong>#{{ Sentry::getLastEventID() }}</strong> bearbeitet.<br><br>
                        In dringenden Fällen kontaktieren Sie uns einfach unter <strong><a href="mailto:support@keeunit.de">support@keeunit.de</a></strong>
                        oder rufen Sie uns an unter <strong>06131 - 930 600 33</strong>.
                    </p>
                    <a href="/" class="ui hollow basic button">
                        Zurück zum Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var colors = [
        '#ff0000',
        '#ffb8ff',
        '#00ffff',
        '#ffb851',
    ];
    var body = document.getElementById('body');
    function changeColor() {
        body.style.fill = colors[Math.floor(Math.random() * colors.length)]
        setTimeout(changeColor, 5000);
    }changeColor();
    var eyes = document.getElementById('pupils-path');
    var cursorX = 0;
    document.addEventListener('mousemove', function(event) {
        cursorX = event.pageX;
    });
    function moveEyes() {
        var position = Math.round(cursorX / document.body.clientWidth * 14);
        eyes.style.transform = 'translateX(-' + position + '%)';
        requestAnimationFrame(moveEyes);
    }
    moveEyes();
</script>
</body>
</html>
