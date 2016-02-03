<?php

include_once "search.php";

if(isset($_POST['submit_search']) && !empty($_POST['queryString'])){
	$qry = $_POST['queryString'];
	$ds = (isset($_POST['datasources']) ? $_POST['datasources'] : array("mospace","pubmed", "ieee", "news", "events"));
	$num_of_records = (isset($_POST['num_of_records']) ? $_POST['num_of_records'] : 10);

	$contents = array("key" => $qry, "data" => search($qry, $ds, $num_of_records));
}
elseif(!isset($_POST['submit_search'])){
	die("You should not call this script directly");
}
elseif(empty($_POST['queryString'])){
	die("You have to type in something to search");
}

//echo "<pre>";
//print_r($contents);
//echo "</pre>";
//exit();
?>

<html>
<head>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
	
<!--<script src="http://cdn.bootcss.com/jquery/1.10.2/jquery.min.js"></script>
<!--<script src="http://babbage.cs.missouri.edu/~ymnhc/bootstrap/dist/js/bootstrap.min.js"></script>-->

<script type="text/javascript">
    $(document).ready(function(){
        $("#goToTop").hide()
        $(function(){
            $(window).scroll(function(){
                if($(this).scrollTop()>1){
                    $("#goToTop").fadeIn();
                } else {
                    $("#goToTop").fadeOut();
                }
            });
        });
		
		$(".toggleMoreAbstract").click(function(){
			$(this).closest('div').find(".moreAb").toggle();
			$(this).html($(this).text() == 'Show Less' ? 'Show More' : 'Show Less');
		});
		
        $("#goToTop").click(function(){
            $("html,body").animate({scrollTop:0},800);
            return false;
        });
        
    });
</script>
  
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    	<title>MU RESEARCH</title>		   


	<style type="text/css">
	
	button {
     background:none!important;
     border:none; 
     padding:0!important;
     font-style: italic;
	 color: blue;
     cursor: pointer;
	}

  .alter{
			width:300px;
		}
   #goToTop {
   }
	.btn-w3r {
    background: linear-gradient(to bottom, #CBD1D1  0%,#CBD1D1  50%,#CBD1D1  100%);   
	color:#fff;
    width: 80px;
    height: 80px;
    -moz-border-radius: 50%;
    -webkit-border-radius: 50%;
    border-radius: 50%;
	}
	
	body{ font-family:'Arial';}
	h1 { background-color: #F0F0F0;}
	
	    #title1 {
     	color: #00F;
		font-family:'Arial';
		
       }
	   
     #title2 {
	  color: #F00; 
	  font-family:'Arial';
     }
    #title3 {
	 color: #FFCC66; 
	 font-family:'Arial';
    }
    #title4 {
	color: #0F0;
	font-family:'Arial';
    }
	.header {
    position:relative;
    z-index:2;
    margin:0 auto;
    padding:0 40px;
    background:#222;
    background:-moz-linear-gradient(0% 100% 90deg, #2b5797, #2b5797);
    background:-webkit-gradient(linear, 0% 0%, 0% 100%, from(#2b5797), to(#2b5797));
    border:1px solid #111;
    -webkit-border-radius:4px 4px 0 0;
    -moz-border-radius:4px 4px 0 0;
    border-radius:4px 4px 0 0;
    -moz-box-shadow:inset 0 1px 0 0 #383838, 0 1px 6px 0  rgba(0,0,0,0.2);
-webkit-box-shadow:inset 0 1px 0 0 #383838, 0 1px 4px 0  rgba(0,0,0,0.2);
 
}
		.highlight { background: #FFFF40; }
		.searchheading { font-size: 130%; font-weight: bold; position:relative;left:250px;width:500px;}
		.summary { font-size: 80%; font-style: italic;  position:relative;left:250px;width:600px;}
		.suggestion { font-size: 100%; }
		.results { font-size: 100%; }
		.category { color: #999999; }
		.sorting { text-align: right; }

		.result_title { font-size: 100%; }		
		.description { font-size: 100%; color: #008000; }
		.context { font-size: 100%; }
		.infoline { font-size: 80%; font-style: normal; color: #808080; position:relative;left:250px;width:300px;}

		.zoom_searchform { font-size: 100%; }
		.zoom_results_per_page { font-size: 80%; margin-left: 10px; }
		.zoom_match { font-size: 80%; margin-left: 10px;}				
		.zoom_categories { font-size: 80%; }
		.zoom_categories ul { display: inline; margin: 0px; padding: 0px;}
		.zoom_categories li { display: inline; margin-left: 15px; list-style-type: none; }
		
		.cat_summary ul { margin: 0px; padding: 0px; display: inline; }
		.cat_summary li { display: inline; margin-left: 15px; list-style-type: none; }		
		
		input.zoom_button {  }
		input.zoom_searchbox {  }		
		
		.result_image { float: left; display: block; }
		.result_image img { margin: 10px; width: 80px; border: 0px; }

		.result_block { margin-top: 15px; margin-bottom: 15px; clear: left; }
		.result_altblock { margin-top: 15px; margin-bottom: 15px; clear: left; }
		
		.result_pages { font-size: 100%; }
		.result_pagescount { font-size: 100%; }
		
		.searchtime { font-size: 80%; }
		
		.recommended 
		{ 
			background: #DFFFBF; 
			border-top: 1px dotted #808080; 
			border-bottom: 1px dotted #808080; 
			margin-top: 15px; 
			margin-bottom: 15px; 
		}
		.recommended_heading { float: right; font-weight: bold; }
		.recommend_block { margin-top: 15px; margin-bottom: 15px; clear: left; }		
		.recommend_title { font-size: 100%; }
		.recommend_description { font-size: 100%; color: #008000; }
		.recommend_infoline { font-size: 80%; font-style: normal; color: #808080;}
		.recommend_image { float: left; display: block; }
		.recommend_image img { margin: 10px; width: 80px; border: 0px; }
	</style>
</head>
  	
<body>
  <!--<div class = "header">
  <a href="http://muresearch.missouri.edu">
	 <div style="width: 50%; margin: auto; margin-top: 1em">
		  <strong style="color:white; position: relative; top: 5; font-size: 25px">MU RESEARCH</strong>
		  <input type="text" name ="key" style="border-radius:7px; width: 40%; height: 35px" required autofocus>
	 </div>
  </a>
  </div>-->
  <div class="header">
	  <div style="width: 50%; margin: auto; padding: 10px; text-align: center">
		  <a href="http://muresearch.missouri.edu"><strong style="color:white; position: relative; font-size: 25px">MU RESEARCH</strong></a>
	  </div>
  </div>

		<br>	
		<br/>
		<div class="col-md-6" style="position:absolute;left:55px;top:100px;">
			<div class="searchheading">Search results for: <?php echo $contents['key'] ?></div>
				<div class="infoline"></div> 
		</div>		
		<br/>  
		<div class="container" style="position:absolute;top:160px;left:15%;">		
			<div class="row" style="position:relative; width: 100%">
				<div class="col-md-8">
					<div class="panel panel-default">
						<div class="panel-body">
						
<?php
$id = 1;
/************MOSpace***********/

if(array_key_exists("mospace", $contents['data']) && !empty($contents['data']['mospace'])){
	$contents['data']['mospace'] = (isset($contents['data']['mospace'][0])) ? $contents['data']['mospace'] : array($contents['data']['mospace']);
	foreach($contents['data']['mospace'] as $c){
?>
		<div class="results">
			<div class="result_block">
				<div class="result_title"><b><?php echo $id?>.</b>&nbsp;<a href=<?php echo $c['url']?>><strong><font size="4"><?php echo $c['title']?></font></strong></a></div>
				<div class="context"><font size="2">datasource: mospace</font></div>
				<div class="context"><font size="2">authors: <?php echo $c['authors']; ?></font></div>
				<div class="context"><font size="2">abstract: <p><?php echo implode(".", array_slice(explode(".", $c['abstract']), 0, 2))?>
															<span class="moreAb" style="display: none"><?php echo implode(".", array_slice(explode(".", $c['abstract']), 2))?></span>
									</font><span><button class="toggleMoreAbstract">Show More</button></span></div>
				<div style="color:green"> &nbsp; - &nbsp;URL:<?php echo $c['url']?></font></div>
			</div>
		</div>
<?php
		++$id;
	}
}
/************Pubmed***********/


if(array_key_exists("pubmed", $contents['data']) && !empty($contents['data']['pubmed'])){
	$contents['data']['pubmed'] = (isset($contents['data']['pubmed'][0])) ? $contents['data']['pubmed'] : array($contents['data']['pubmed']);
	foreach($contents['data']['pubmed'] as $c){
?>
		<div class="results">
			<div class="result_block">
				<div class="result_title"><b><?php echo $id?>.</b>&nbsp;<a href=<?php echo $c['url']?>><strong><font size="4"><?php echo $c['title']?></font></strong></a></div>
				<div class="context"><font size="2">datasource: pubmed</font></div>
				<div class="context"><font size="2">authors: <?php echo implode("; ", explode("|", $c['authors']))?></font></div>
				<div class="context"><font size="2">abstract: <p><?php echo implode(".", array_slice(explode(".", $c['abstract']), 0, 2))?>
															<span class="moreAb" style="display: none"><?php echo implode(".", array_slice(explode(".", $c['abstract']), 2))?></span>
									</font><span><button class="toggleMoreAbstract">Show More</button></span></div>
				<div style="color:green"> &nbsp; - &nbsp;URL:<?php echo $c['url']?></font></div>
			</div>
		</div>
<?php
		++$id;
	}
}

/******** ieee ********/
if(array_key_exists("ieee", $contents['data']) && !empty($contents['data']['ieee'])){
	$contents['data']['ieee'] = (isset($contents['data']['ieee'][0])) ? $contents['data']['ieee'] : array($contents['data']['ieee']);
	foreach($contents['data']['ieee'] as $c){
?>
		<div class="results">
			<div class="result_block">
				<div class="result_title"><b><?php echo $id?>.</b>&nbsp;<a href=<?php echo $c['mdurl']?>><strong><font size="4"><?php echo $c['title']?></font></strong></a></div>
				<div class="context"><font size="2">datasource: IEEE</font></div>
				<div class="context"><font size="2">authors: <?php echo implode("; ", explode("|", $c['authors']))?></font></div>
				<div class="context"><font size="2">abstract: <p><?php echo implode(".", array_slice(explode(".", $c['abstract']), 0, 2))?>
																<span class="moreAb" style="display: none"><?php echo implode(".", array_slice(explode(".", $c['abstract']), 2))?></span>
																</font><span><button class="toggleMoreAbstract">Show More</button></span></div>
				<div style="color:green"> &nbsp;- &nbsp;URL:<?php echo $c['mdurl']?></font></div>
			</div>
		</div>
<?php
		++$id;
	}

}

/**************News**************/
if(array_key_exists("news", $contents['data']) && !empty($contents['data']['news'])){
	$contents['data']['news'] = (isset($contents['data']['news'][0])) ? $contents['data']['news'] : array($contents['data']['news']);
	foreach($contents['data']['news'] as $c){
?>
		<div class="results">
			<div class="result_block">
				<div class="result_title"><b><?php echo $id?>.</b>&nbsp;<a href=<?php echo $c['link']?>><strong><font size="4"><?php echo $c['title']?></font></strong></a></div>
				<div class="context"><font size="2">datasource: Mizzou News</font></div>
				<div class="context"><font size="2">publication date: <?php echo $c['pubdate']?></font></div>
				<div style="color:green"> &nbsp;- &nbsp;URL:<?php echo $c['link']?></div>
			</div>
		</div>
<?php
		++$id;
	}
}

/*************Events*************/
if(array_key_exists("events", $contents['data']) && !empty($contents['data']['events'])){
	$contents['data']['events'] = (isset($contents['data']['events'][0])) ? $contents['data']['events'] : array($contents['data']['events']);
	foreach($contents['data']['events'] as $c){
?>
		<div class="results">
			<div class="result_block">
				<div class="result_title"><b><?php echo $id?>.</b>&nbsp;<a href=<?php echo $c['link']?>><strong><font size="4"><?php echo $c['title']?></font></strong></a></div>
				<div class="context"><font size="2">datasource: Mizzou Events</font></div>
				<div class="context"><font size="2">Description: <p><?php echo implode(".", array_slice(explode(".", $c['description']), 0, 2))?>
																<span class="moreAb" style="display: none"><?php echo implode(".", array_slice(explode(".", $c['description']), 2))?></span>
																</font><span><button class="toggleMoreAbstract">Show More</button></span></div>
				<div class="context"><font size="2">Begin date: <?php echo $c['start']?></font></div>
				<div class="context"><font size="2">Location: <?php echo $c['venue']?></font></div>
				<div style="color:green"> &nbsp;- &nbsp;URL:<?php echo $c['link']?></font></div>
			</div>
		</div>
<?php
		++$id;
	}
}
?>
					</div>
				</div>
			</div>

			<div class="col-md-4">
				<div class="panel panel-default">
					<div class="panel-body">
<?php
if(array_key_exists("contacts", $contents['data']) && !empty($contents['data']['contacts'])){
	$contents['data']['contacts'] = (isset($contents['data']['contacts'][0])) ? $contents['data']['contacts'] : array($contents['data']['contacts']);
?>
	<div class="context" style="color:grey"><strong><font size="2">Suggested Contacts:</font></div>
<?php
	foreach($contents['data']['contacts'] as $c){
		$name = $c['FirstName']." ".$c['LastName'];
		$p_url = "profile.php?name=".urlencode($name);
?>
		<div class="results">
			<div class="result_block">
				<div class="result_title"><img src="./img/user.png" width="40" height="40"></img>&nbsp;&nbsp;<a href=<?php echo $p_url?>><strong><font size="4"><?php echo $name?></font></strong></a></div>
				<div class="context" style="color:grey"><font size="2"><?php echo $c['Title']; ?><br><?php echo $c['Department']?></font></div>
				<div class="context" style="color:grey"><font size="2">University of Missouri</font></div>
				<div style="color:green">Email: <?php echo $c['Email']?> &nbsp;  &nbsp;</div>
			</div>
		</div>

<?php
	}
}
?>

					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
