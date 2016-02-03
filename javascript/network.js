//This function loads cytoscape network
function loadNetwork(unifiedName, cy){

    var data = JSON.stringify({'name': unifiedName});

    $.ajax({
        url: 'getCytoscapeJSON.php',
        type: 'POST',
        data: {data: data},
        dataType: 'json',
        cache: false,
        success: function(data){

            console.log(data);
            cy.startBatch();
            cy.load(data);
            cy.endBatch();

            cy.style()
                .selector('node[name = "' + unifiedName + '" ]')
                .css({'background-color': 'red'})
                .update();

            cy.style()
                .selector('node[name != "' + unifiedName + '" ]')
                .css({'background-color': 'gray'})
                .update();

            cy.style()
                .selector('node[fromMU = 1]')
                .css({'background-color': '#F1B82D',
                    'border-width': '4px',
                    'border-color': '#000000'})
                .update();

        },

        error:function(x,e) {
            if (x.status==0) {
                console.log('You are offline');
            } else if(x.status==404) {
                console.log('Requested URL not found.');
            } else if(x.status==500) {
                console.log('Internel Server Error.');
            } else if(e=='parsererror') {
                console.log('Error. Parsing JSON Request failed.');
            } else if(e=='timeout'){
                console.log('Request Time out.');
            } else {
                console.log('Unknow Error: '+x.responseText);
            }
        }
    });
}

function loadPublication(unifiedName){
    var data = JSON.stringify({'name': unifiedName});

    $.ajax({
        url: 'getPublicationJSON.php',
        type: 'POST',
        data: {data: data},
        dataType: 'json',
        cache: false,
        success: function(data){
            $("#accordion").empty();
            data.forEach(function(jsonObj){
                var div = document.createElement('div');
                div.className = 'panel-heading';
                div.innerHTML = '<h4 class="panel-title"><a href="'+jsonObj['url']+'">'+jsonObj['title']+'</a></h4>';
                document.getElementById('accordion').appendChild(div);
            });
        },

        error:function(x,e) {
            if (x.status==0) {
                console.log('You are offline');
            } else if(x.status==404) {
                console.log('Requested URL not found.');
            } else if(x.status==500) {
                console.log('Internel Server Error.');
            } else if(e=='parsererror') {
                console.log('Error. Parsing JSON Request failed.');
            } else if(e=='timeout'){
                console.log('Request Time out.');
            } else {
                console.log('Unknow Error: '+x.responseText);
            }
        }
    });
}