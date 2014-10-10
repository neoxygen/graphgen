$(document).ready(function() {

    $('.exportButtons').hide();
    // Instantiate code editor

    var editor = CodeMirror.fromTextArea(document.getElementById("patternBox"), {
        lineNumbers: true,
        mode: "application/x-cypher-query",
        matchBrackets: true,
        autofocus: true,
        theme: 'neo'
    });


    $('#patternForm').submit(function (e) {
        e.preventDefault();
        var apiEndpoint = $(this).attr('action');
        var pattern = $('#patternBox').val();
        var posting = $.post(apiEndpoint, {'pattern': pattern});
        posting.done(function (data) {
            $('#cypherError').hide();
            var data = $.parseJSON(data);
            console.log(data);
            g = {
                nodes: [],
                edges: []
            };
            $.each(data.nodes, function (index, node) {
                g.nodes.push({
                    id: node._id,
                    label: node.laebl,
                    caption: node.label,
                });
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
                forceLocked: true,
                collisionDetection: true,
                zoomControls: true,
                initialScale: 0.5,
                cluster: true,
                nodeTypes: {"node_type":
                    [
                        "Person",
                        "Country",
                        "Company",
                        "Skill",
                        //"other"
                    ]
                },
                rootNodeRadius: 30,
                toggleRootNodes: false
            };
            $('#gjson_result').html(pattern);
            $('#alchemy').css('min-height', '600px');
            alchemy.begin(config);
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
});