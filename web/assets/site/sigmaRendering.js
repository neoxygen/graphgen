<script type="text/javascript">
$(document).ready(function(){
    $('#patternForm').submit(function(e){
        e.preventDefault();
        var apiEndpoint = $(this).attr('action');
        var pattern = $('#patternBox').val();
        var posting = $.post(apiEndpoint, {'pattern': pattern });
        posting.done(function(data){
            var data = $.parseJSON(data);
            g = {
                nodes: [],
                edges: []
            };
            $.each(data.nodes, function(index, node){
                g.nodes.push({
                    id: node.id,
                    label: node.type,
                    color: node.neogen_color
                });
            });
            $.each(data.edges, function(index, edge){
                g.edges.push({
                    source: edge.source,
                    target: edge.target,
                    caption: edge.type
                });
            });


        });
    });
</script>