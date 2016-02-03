<?php

//Array => (
//  [0] => Array
//  (
//	    [title] => RNA-protein distance patterns in ribosomes reveal the mechanism of translational attenuation.
//      [url] => http://www.ncbi.nlm.nih.gov/pubmed/25326828
//      [people] => Array
//      (
//	        [0] => Array
//	        (
//		        [firstName] => DongMei
//              [lastName] => Yu
//              [affiliation] => Department of Biological Engineering, University of Missouri, Columbia, MO, 65211, USA.
//          )

//          [1] => Array
//          (
//	            [firstName] => Chao
//              [lastName] => Zhang
//              [affiliation] =>
//          )
//      )
//  )

//  [1] => Array
//  (
//	    [title] => DNA explanation
//      [url] => http://www.ncbi.nlm.nih.gov/pubmed/25326878
//      [people] => Array
//      (
//	        [0] => Array
//	        (
//		        [firstName] => Haotian
//              [lastName] => Li
//              [affiliation] => Department of Biological Engineering, University of Missouri, Columbia, MO, 65211, USA.
//          )
//          [1] => Array
//          (
//	            [firstName] => Zhong
//              [lastName] => Li
//              [affiliation] => Department of Computer Science, Universtiy of Delaware
//          )
//          [2] => Array
//          (
//	            [firstName] => Wenbo
//              [lastName] => Guo
//              [affiliation] =>
//          )
//      )
//  )
//}
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

<script>
$(function(){

    var data = [{"title": "RNA-protein distance patterns in ribosomes reveal the mechanism of translational attenuation.", "url": "http://www.ncbi.nlm.nih.gov/pubmed/25326828", "people": [{"firstname": "Chang", "lastname": "Liu", "affiliation": "Department of computer Science"},{"firstname": "Guang", "lastname": "Li", "affiliation": "Department of computer Science"}]},
        {"title": "DNA explanation", "url": "http://www.ncbi.nlm.nih.gov/pubmed/25326878", "people": [{"firstname": "Haotian", "lastname": "Li", "affiliation": "Department of Computer Science"}, {"firstname": "Wu", "lastname": "Guoguang", "affiliation": "Department of Computer"}, {"firstname": "Wenbo", "lastname": "Guo", "affiliation": "Department of Computer Sci"}]}];

    $.ajax({
        url: 'AddGraphDatabaseInterface.php',
        type: 'POST',
        data: {data: JSON.stringify({'data': data})},
        dataType: "text",
        success: function (data) {
            console.log(data);
        },

        error: function (x, e) {
            if (x.status == 0) {
                console.log('You are offline');
            } else if (x.status == 404) {
                console.log('Requested URL not found.');
            } else if (x.status == 500) {
                console.log('Internel Server Error.');
            } else if (e == 'parsererror') {
                console.log('Error. Parsing JSON Request failed.');
            } else if (e == 'timeout') {
                console.log('Request Time out.');
            } else {
                console.log('Unknow Error: ' + x.responseText);
            }
        }
    })
})
</script>


