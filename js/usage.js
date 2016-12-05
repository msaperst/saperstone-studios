$(document).ready(function() {
    var length = 7;
    var start = -1 * length;

    $('#hit-usage').height($('#hit-usage').width());
    generateHitGraph(length, start);

    $('#page-usage').height($('#page-usage').width());
    generatePageGraph(length, start);

    $('#device-usage').height($('#device-usage').width());
    generateDeviceGraph();

    $('#os-usage').height($('#os-usage').width());
    generateOSGraph();

    $('#browser-usage').height($('#browser-usage').width());
    generateBrowserGraph("");

    $('#screen-usage').height($('#screen-usage').width());
    generateScreenGraph();

    $('#over-time-usage-prev').click(function() {
        start -= length;
        generateHitGraph(length, start);
        generatePageGraph(length, start);
    });
    $('#over-time-usage-now').click(function() {
        start = -7;
        generateHitGraph(length, start);
        generatePageGraph(length, start);
    });
    $('#over-time-usage-next').click(function() {
        start += length;
        generateHitGraph(length, start);
        generatePageGraph(length, start);
    });
    $('#over-time-usage-year').click(function() {
        length = 365;
        generateHitGraph(length, start);
        generatePageGraph(length, start);
    });
    $('#over-time-usage-month').click(function() {
        length = 31;
        generateHitGraph(length, start);
        generatePageGraph(length, start);
    });
    $('#over-time-usage-week').click(function() {
        length += 7;
        generateHitGraph(length, start);
        generatePageGraph(length, start);
    });

    $('#browser-usage-restart').click(function() {
        generateBrowserGraph("");
    });
});

function generateHitGraph(length, start) {
    var dataPoints = [];
    $.getJSON("/api/usage-hit.php?length=" + length + "&start=" + start, function(data) {
        $.each(data, function(key, value) {
            dataPoints.push({
                x : new Date(key),
                y : parseInt(value)
            });
        });
        var chart = new CanvasJS.Chart("hit-usage", {
            title : {
                text : "Overall Site Hits"
            },
            data : [ {
                type : "line",
                dataPoints : dataPoints,
            } ]
        });
        chart.render();
    });
}

function generatePageGraph(length, start) {
    var dataP = [];
    $.getJSON("/api/usage-page.php?length=" + length + "&start=" + start, function(data) {
        $.each(data, function(key, value) {
            var dataPoints = [];
            $.each(value, function(k, v) {
                dataPoints.push({
                    x : new Date(k),
                    y : parseInt(v)
                });
            });
            dataP.push({
                type : "stackedColumn",
                legendText : key,
                showInLegend : "true",
                toolTipContent : "{legendText}",
                dataPoints : dataPoints
            });
        });
        var chart = new CanvasJS.Chart("page-usage", {
            title : {
                text : "Overall Page Hits"
            },
            data : dataP
        });
        chart.render();
    });
}

function generateOSGraph() {
    var dataPoints = [];
    $.getJSON("/api/usage-os.php", function(data) {
        $.each(data, function(key, value) {
            dataPoints.push({
                indexLabel : key,
                y : parseInt(value)
            });
        });
        var chart = new CanvasJS.Chart("os-usage", {
            title : {
                text : "Overall Operating System Usage"
            },
            data : [ {
                type : "pie",
                showInLegend : true,
                toolTipContent : "{y} - #percent %",
                legendText : "{indexLabel}",
                dataPoints : dataPoints,
            } ]
        });
        chart.render();
    });
}

function generateDeviceGraph() {
    var dataPoints = [];
    $.getJSON("/api/usage-device.php", function(data) {
        $.each(data, function(key, value) {
            dataPoints.push({
                indexLabel : key,
                y : parseInt(value)
            });
        });
        var chart = new CanvasJS.Chart("device-usage", {
            title : {
                text : "Overall Device Usage"
            },
            data : [ {
                type : "pie",
                showInLegend : true,
                toolTipContent : "{y} - #percent %",
                legendText : "{indexLabel}",
                dataPoints : dataPoints,
            } ]
        });
        chart.render();
    });
}

function generateBrowserGraph(urlPlus) {
    var dataPoints = [];
    $.getJSON("/api/usage-browser.php?" + urlPlus, function(data) {
        $.each(data, function(key, value) {
            dataPoints.push({
                indexLabel : key,
                y : parseInt(value)
            });
        });
        var chart = new CanvasJS.Chart("browser-usage", {
            title : {
                text : "Overall Browser Usage"
            },
            data : [ {
                type : "pie",
                click : function(e) {
                    console.log(e);
                    generateBrowserGraph("browser=" + e.dataPoint.indexLabel);
                },
                showInLegend : true,
                toolTipContent : "{y} - #percent %",
                legendText : "{indexLabel}",
                dataPoints : dataPoints,
            } ]
        });
        chart.render();
    });
}

function generateScreenGraph() {
    var dataPoints = [];
    $.getJSON("/api/usage-screen.php", function(data) {
        $.each(data, function(key, value) {
            dataPoints.push({
                indexLabel : key,
                y : parseInt(value)
            });
        });
        var chart = new CanvasJS.Chart("screen-usage", {
            title : {
                text : "Overall Screen Size Usage"
            },
            data : [ {
                type : "pie",
                showInLegend : true,
                toolTipContent : "{y} - #percent %",
                legendText : "{indexLabel}",
                dataPoints : dataPoints,
            } ]
        });
        chart.render();
    });
}