$(document).ready(function(){
    var popForm = $('form#populator');
    $('#triggerPopBox').click(function(e){
        if (!$('#gjson_result').is(':empty')){
            resetBoxContents();
        } else {
            console.log('No graph has been generated');
            e.stopPropagation();
        }
    });
    popForm.submit(function(e){
        e.preventDefault();
        $('form#populator :input').prop('disabled', true);
        var url = getUrl();
        var queriesEndpoint = popForm.attr('action');
        var emptyDB = $('#populate-empty-condition').is(':checked');
        var pattern = $('#gjson_result').html();
        var postCypher = $.post(queriesEndpoint, {'pattern': pattern});
        postCypher.done(function (data) {

            if (emptyDB){
                var emptyStatement = JSON.stringify({
                    statements: [{
                        statement: 'MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n'
                    }]
                });
                sendStatement(url, emptyStatement);
                console.log('Database cleared')
            }

            var queries = JSON.parse(data);

            if (sendConstraints(url, queries.constraints) != true){
                return false;
            }

            if (!loadNodes(url, queries.nodes)){
                return false;
            }

            if (!loadEdges(url, queries.edges)){
                return false;
            }

            var msg = 'Data successfully loaded in your database. ' +
                'Open it <a href="' + getBrowserUrl() + '" target="_blank">' + getBrowserUrl() + '</a>';
            $('#popResult').html('<div class="alert alert-success" role="alert">' + msg + '</div>');
            $('form#populator :input').prop('disabled', false);

        });
    });

    function getUrl()
    {
        var url = $('#populate-location').val() + '/db/data/transaction/commit';
        return url;
    }

    function getBrowserUrl()
    {
        var url = $('#populate-location').val() + '/browser';
        return url;
    }

    function sendStatement(endpoint, data)
    {
        var neoError;
        $.ajax({
            url: endpoint,
            type: "POST",
            data: data,
            async: false,
            contentType: "application/json"
        })
            .done(function(result){
                neoError = result.errors;

            })
            .fail(function(error){
                console.log(error.statusText);
                displayError(error.statusText);
                return false;
            });
        if (neoError.length == 0){
            return true;
        } else {
            displayError(neoError.message)
            console.log(neoError);
            return false;
        }
    }

    function resetBoxContents()
    {
        $('#popResult').html('');
    }

    function displayError(error)
    {
        $('#popResult').html('<div class="alert alert-danger" role="alert">' + error + '</div>');
        $('form#populator :input').prop('disabled', false);
    }

    function sendConstraints(url, constraints){
        $.each(constraints, function(index, constraint){
            var body = JSON.stringify({
                statements: [{
                    statement: constraint
                }]
            });
            if (!sendStatement(url, body)){
                return false;
            }
        });
        console.log('Constraints created');
        return true;
    }

    function loadNodes(url, nodes){
        $.each(nodes, function(index, nodeMap){
            var nodesBody = JSON.stringify({
                statements: [{
                    statement:  nodeMap.statement,
                    parameters: nodeMap.parameters
                }]
            });
            if (!sendStatement(url, nodesBody))
            {
                return false;
            }
        });
        console.log('Nodes created');
        return true;
    }

    function loadEdges(url, edges){
        $.each(edges, function(index, edgeMap){
            var edgesBody = JSON.stringify({
                statements: [{
                    statement:  edgeMap.statement,
                    parameters: edgeMap.parameters
                }]
            });
            if (!sendStatement(url, edgesBody)){
                return false;
            }
        });
        console.log('Edges created');
        return true;
    }
});