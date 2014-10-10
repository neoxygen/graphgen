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
                res = sendStatement(url, emptyStatement);
                if (!res){ return false; }

            }

            var queries = JSON.parse(data);
            $.each(queries.constraints, function(index, constraint){
                var body = JSON.stringify({
                    statements: [{
                        statement: constraint
                    }]
                });
                sendStatement(url, body);
            });

            $.each(queries.nodes, function(index, nodeMap){
                var nodesBody = JSON.stringify({
                    statements: [{
                        statement:  nodeMap.statement,
                        parameters: nodeMap.parameters
                    }]
                });
                sendStatement(url, nodesBody);
            });

            $.each(queries.edges, function(index, edgeMap){
                var edgesBody = JSON.stringify({
                    statements: [{
                        statement:  edgeMap.statement,
                        parameters: edgeMap.parameters
                    }]
                });
                sendStatement(url, edgesBody);
            });

            var msg = 'Data successfully loaded in your database.';
            $('#popResult').html('<div class="alert alert-success" role="alert">' + msg + '</div>');
            $('form#populator :input').prop('disabled', false);

        });
    });

    function getUrl()
    {
        var url = $('#populate-location').val() + '/db/data/transaction/commit';
        return url;
    }

    function sendStatement(endpoint, data)
    {
        $.ajax({
            url: endpoint,
            type: "POST",
            data: data,
            async: false,
            contentType: "application/json"})
            .fail(function(error){
                console.log(error);
                displayError(error.statusText);
                return false;
            })
            .done(function(res){
                return true;
            });
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
});