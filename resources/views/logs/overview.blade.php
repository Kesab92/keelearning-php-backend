<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    </head>
    <body>
        <div class="row">
            <div class="col-sm-12 col-md-6">
                <h1>Durchschnittliche Antwortzeit</h1>
                <canvas id="avgresponse" width="1600" height="400"></canvas>
            </div>
            <div class="col-sm-12 col-md-6">
                <h1>Requests pro Stunde</h1>
                <canvas id="requestcount" width="1600" height="400"></canvas>
            </div>
        </div>
        
        <h1>Peak: {{ $peak['requests']/10 }} Anfragen pro Sekunde um {{ $peak['time'] }}</h1>
        
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>URL</th>
                    <th>Durchschnittliche Antwortzeit (in Sekunden)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($byurl as $url=>$time)
                    <tr>
                        <td>{{ $url }}</td>
                        <td>{{ $time }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.6/Chart.bundle.js"></script>
        <script>
            var myLineChart = new Chart(document.getElementById("avgresponse"), {
                type: 'line',
                data: {
                    labels: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24],
                    datasets: [{
                        lineTension: 0,
                        label: 'Durchschnittliche Antwortzeit pro Stunde (in Sekunden)',
                        data:{!!  json_encode($avgresponse)  !!}
                    }]
                }
            });

            var myLineChart = new Chart(document.getElementById("requestcount"), {
                type: 'line',
                data: {
                    labels: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24],
                    datasets: [{
                        lineTension: 0,
                        label: 'Anzahl an Requests pro Stunde',
                        data:{!!  json_encode($requestcount)  !!}
                    }]
                }
            });
        </script>
    </body>
</html>
