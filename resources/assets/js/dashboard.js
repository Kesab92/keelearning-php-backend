/**
 * Dashboard charts
 */
var ch = null;
$(function() {
    $(".pie-chart").each(function() {
        var percentage = $(this).data("percentage").toFixed(2);
        var data = {
            labels: [
                "Richtig",
                "Falsch"
            ],
            datasets: [
                {
                    data: [parseFloat(percentage), parseFloat((100 - parseFloat(percentage)).toFixed(2))],
                    backgroundColor: [
                        '#2185D0', '#eeeeee'
                    ]
                } 
            ]
        };
        
        var size = $(this).width() - 40;
        
        $(this).append("<canvas style='width:" + size + ";height:" + size + "'></canvas>");
        new Chart($(this).find("canvas").get(0).getContext("2d"), {
            type: 'doughnut',
            data: data,
            options: {
                cutoutPercentage: 92,
                legend: {
                    display: false
                }
            }
        })
    });

    setupLatestGameCountsChart();
    
    function setupLatestGameCountsChart() {
        var data = {
            labels: Object.keys(latestGameCounts),
            datasets: [
                {
                    label: "Gestartete Spiele",

                    // Boolean - if true fill the area under the line
                    fill: true,

                    // String - the color to fill the area under the line with if fill is true
                    backgroundColor: "rgba(0,121,214,0.2)",

                    // String or array - Line color
                    borderColor: "rgba(0,121,214,1)",

                    // String or array - Point stroke color
                    pointBorderColor: "rgba(0,121,214,1)",

                    // String or array - Point fill color
                    pointBackgroundColor: "#fff",
                    // String or array - point background color when hovered
                    pointHoverBackgroundColor: "rgba(220,220,220,1)",

                    // Point border color when hovered
                    pointHoverBorderColor: "rgba(220,220,220,1)",
                    // The actual data
                    data: $.map(latestGameCounts, function (value, index) {
                        return value;
                    })

    }
            ]
        };

        ch = new Chart($(".latest-game-counts canvas"), {
            type: 'line',
            data: data,
            options: {
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [
                        {
                            display: false
                        }
                    ],
                    xAxes: [
                        {
                            gridLines: {
                                drawTicks: false
                            }
                        }
                    ]
                }
            }
        });
        
    }
    
});