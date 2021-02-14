var length = 7;
var start = -1 * length;
var ignoreAdmin = 0;

$(document).ready(function () {
    $('#ignore-admins-input').change(function () {
        ignoreAdmin = $(this).is(':checked') ? 1 : 0;
        generatePageGraph();
        generateHitGraph();
        generateUniqueGraph();
        generateDeviceGraph();
        generateOSGraph();
        generateBrowserGraph("");
        generateScreenGraph();
    });

    $('#page-usage').height($('#page-usage').width());
    generatePageGraph();

    $('#hit-usage').height($('#hit-usage').width());
    generateHitGraph();

    $('#unique-usage').height($('#unique-usage').width());
    generateUniqueGraph();

    $('#device-usage').height($('#device-usage').width());
    generateDeviceGraph();

    $('#os-usage').height($('#os-usage').width());
    generateOSGraph();

    $('#browser-usage').height($('#browser-usage').width());
    generateBrowserGraph("");

    $('#screen-usage').height($('#screen-usage').width());
    generateScreenGraph();

    $('#over-time-usage-prev').click(function () {
        start -= length;
        generatePageGraph();
        generateHitGraph();
        generateUniqueGraph();
    });
    $('#over-time-usage-now').click(function () {
        start = length * -1;
        generatePageGraph();
        generateHitGraph();
        generateUniqueGraph();
    });
    $('#over-time-usage-next').click(function () {
        start += length;
        generatePageGraph();
        generateHitGraph();
        generateUniqueGraph();
    });
    $('#over-time-usage-year').click(function () {
        length = 365;
        generatePageGraph();
        generateHitGraph();
        generateUniqueGraph();
    });
    $('#over-time-usage-month').click(function () {
        length = 31;
        generatePageGraph();
        generateHitGraph();
        generateUniqueGraph();
    });
    $('#over-time-usage-week').click(function () {
        length += 7;
        generatePageGraph();
        generateHitGraph();
        generateUniqueGraph();
    });

    $('#browser-usage-restart').click(function () {
        generateBrowserGraph("");
    });
});

function generatePageGraph() {
    var dataP = [];
    $.getJSON("/api/usage-page.php?noadmin=" + ignoreAdmin + "&length=" + length + "&start=" + start, function (data) {
        $.each(data, function (key, value) {
            var dataPoints = [];
            $.each(value, function (k, v) {
                dataPoints.push({
                    x: new Date(k),
                    y: parseInt(v)
                });
            });
            dataP.push({
                type: "stackedColumn",
                legendText: key,
                showInLegend: "true",
                toolTipContent: "<div class='text-center'>{y}<br/>{legendText}<br>{x}</div>",
                dataPoints: dataPoints
            });
        });
        var chart = new CanvasJS.Chart("page-usage", {
            title: {
                text: "Overall Page Hits"
            },
            data: dataP
        });
        chart.render();
    });
}

function generateHitGraph() {
    var dataPoints = [];
    $.getJSON("/api/usage-hit.php?noadmin=" + ignoreAdmin + "&length=" + length + "&start=" + start, function (data) {
        $.each(data, function (key, value) {
            dataPoints.push({
                x: new Date(key),
                y: parseInt(value)
            });
        });
        var chart = new CanvasJS.Chart("hit-usage", {
            title: {
                text: "Overall Site Hits"
            },
            data: [{
                type: "line",
                dataPoints: dataPoints,
            }]
        });
        chart.render();
    });
}

function generateUniqueGraph() {
    var dataPoints = [];
    $.getJSON("/api/usage-unique.php?noadmin=" + ignoreAdmin + "&length=" + length + "&start=" + start, function (data) {
        $.each(data, function (key, value) {
            dataPoints.push({
                x: new Date(key),
                y: parseInt(value)
            });
        });
        var chart = new CanvasJS.Chart("unique-usage", {
            title: {
                text: "Unique Site Hits"
            },
            data: [{
                type: "line",
                dataPoints: dataPoints,
            }]
        });
        chart.render();
    });
}

function generateOSGraph() {
    var dataPoints = [];
    $.getJSON("/api/usage-os.php?noadmin=" + ignoreAdmin, function (data) {
        $.each(data, function (key, value) {
            dataPoints.push({
                indexLabel: key,
                y: parseInt(value)
            });
        });
        var chart = new CanvasJS.Chart("os-usage", {
            title: {
                text: "Overall Operating System Usage"
            },
            data: [{
                type: "pie",
                showInLegend: true,
                toolTipContent: "{y} - #percent %",
                legendText: "{indexLabel}",
                dataPoints: dataPoints,
            }]
        });
        chart.render();
    });
}

function generateDeviceGraph() {
    var dataPoints = [];
    $.getJSON("/api/usage-device.php?noadmin=" + ignoreAdmin, function (data) {
        $.each(data, function (key, value) {
            dataPoints.push({
                indexLabel: key,
                y: parseInt(value)
            });
        });
        var chart = new CanvasJS.Chart("device-usage", {
            title: {
                text: "Overall Device Usage"
            },
            data: [{
                type: "pie",
                showInLegend: true,
                toolTipContent: "{y} - #percent %",
                legendText: "{indexLabel}",
                dataPoints: dataPoints,
            }]
        });
        chart.render();
    });
}

function generateBrowserGraph(urlPlus) {
    var dataPoints = [];
    $.getJSON("/api/usage-browser.php?noadmin=" + ignoreAdmin + "&" + urlPlus, function (data) {
        $.each(data, function (key, value) {
            dataPoints.push({
                indexLabel: key,
                y: parseInt(value)
            });
        });
        var chart = new CanvasJS.Chart("browser-usage", {
            title: {
                text: "Overall Browser Usage"
            },
            data: [{
                type: "pie",
                click: function (e) {
                    generateBrowserGraph("browser=" + e.dataPoint.indexLabel);
                },
                showInLegend: true,
                toolTipContent: "{y} - #percent %",
                legendText: "{indexLabel}",
                dataPoints: dataPoints,
            }]
        });
        chart.render();
    });
}

function generateScreenGraph() {
    var dataPoints = [];
    $.getJSON("/api/usage-screen.php?noadmin=" + ignoreAdmin, function (data) {
        $.each(data, function (key, value) {
            dataPoints.push({
                indexLabel: key,
                y: parseInt(value)
            });
        });
        var chart = new CanvasJS.Chart("screen-usage", {
            title: {
                text: "Overall Screen Size Usage"
            },
            data: [{
                type: "pie",
                showInLegend: true,
                toolTipContent: "{y} - #percent %",
                legendText: "{indexLabel}",
                dataPoints: dataPoints,
            }]
        });
        chart.render();
    });
}