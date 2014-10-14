$(document).ready(function() {

    var graphJson;

    var sheet = (function() {
        // Create the <style> tag
        var style = document.createElement("style");

        // Add a media (and/or media query) here if you'd like!
        // style.setAttribute("media", "screen")
        // style.setAttribute("media", "only screen and (max-width : 1024px)")

        // WebKit hack :(
        style.appendChild(document.createTextNode(""));

        // Add the <style> element to the page
        document.head.appendChild(style);

        return style.sheet;
    })();
    function addCSSRule(sheet, selector, rules, index) {
        if("insertRule" in sheet) {
            sheet.insertRule(selector + "{" + rules + "}", index);
        }
        else if("addRule" in sheet) {
            sheet.addRule(selector, rules, index);
        }
    }




    $('.exportButtons').hide();
    // Instantiate code editor

    var editor = CodeMirror.fromTextArea(document.getElementById("patternBox"), {
        lineNumbers: true,
        mode: "application/x-cypher-query",
        matchBrackets: true,
        autofocus: true,
        theme: 'neo'
    });

    precalculate();

    function precalculate(){
        var transformUrl = $('#patternBox').attr('data-validator');
        var data = editor.getValue();
        $.ajax({
            url: transformUrl,
            type: "POST",
            data: {'pattern': data}
        })
            .done(function(result){
                var graph = $.parseJSON(result);
                $('#precalculate-info').html(graph.nodes.length + ' nodes | ~ ' + graph.edges.length + ' edges');
                return true;

            })
            .fail(function(error){
                return false;
            });
    }

    editor.on("change", function(cm, change) {
        precalculate();
    });


    $('#patternForm').submit(function (e) {
        e.preventDefault();
        var apiEndpoint = $(this).attr('action');
        var pattern = $('#patternBox').val();
        var posting = $.post(apiEndpoint, {'pattern': pattern});
        var clusterColors = ["#DD79FF", "#FFFC00", "#00FF30", "#5168FF", "#00C0FF",
            "#FF004B", "#00CDCD", "#f83f00", "#f800df", "#ff8d8f",
            "#ffcd00", "#184fff", "#ff7e00"];
        var rulesRuled = [];
        var nodeTypes = [];
        posting.done(function (data) {
            $('#cypherError').hide();
            var data = $.parseJSON(data);
            g = {
                nodes: [],
                edges: []
            };
            $.each(data.nodes, function (index, node) {
                g.nodes.push({
                    id: node._id,
                    label: node.label,
                    caption: node.label,
                    node_type: node.label,
                    cluster: node.cluster,
                    properties: node.properties
                });
                if (!(node.label in rulesRuled)){
                    rulesRuled[node.label] = node.cluster;
                    nodeTypes.push(node.label);
                }
            });

            $.each(data.edges, function (index, edge) {
                g.edges.push({
                    source: edge._source,
                    target: edge._target,
                    caption: edge.type
                });
            });

            var config = {
                dataSource: g,
                nodeTypes: {"label": nodeTypes},
                forceLocked: true,
                collisionDetection: true,
                zoomControls: true,
                initialScale: 0.5,
                cluster: false,
                clusterKey: 'cluster',
                rootNodeRadius: 30,
                toggleRootNodes: false,
                nodeMouseOver: function(node){
                    console.log(node);
                    setNodeInfo(node);
                }
            };
            $('#gjson_result').html(JSON.stringify(data));
            $('#alchemy').css('min-height', '600px');
            $('#intro').hide();
            alchemy.begin(config);
            $.each(nodeTypes, function(index, type){
                clust = rulesRuled[type];
                $('.' + type + ' circle').attr('fill', clusterColors[clust]);
                $('.' + type + ' circle').css('fill', clusterColors[clust]);
            });
            $('.exportButtons').show();


        });
        posting.fail(function(data){
            var error = data.responseJSON.error;
            console.log(error);
            $('#cypherError').html('<p>' + error.message + '</p>');
            $('#cypherError').show();
        });

    });

    $('#exportToGraphJson').submit(function(e){
        var pattern = $('#gjson_result').html();
        $('#gjson_pattern').val(pattern);
    });

    $('#exportToCypher').submit(function(e){
        var pattern = $('#gjson_result').html();
        $('#cypher_pattern').val(pattern);
    });

    function setNodeInfo(node)
    {
        var box = $('#nodeInfo');
        box.show();
        box.html('');
        box.append('<h4>' + node.properties.label + '</h4>');
        box.append('<h5>Properties</h5>');
        box.append('<ul>');
        $.each(node.properties.properties, function(index, value){
            box.append('<li>' + index + ' : ' + value + '</li>');
        });
        box.append('</ul>');
    }
});