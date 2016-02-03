<html>
<head>
	<!-- Bootstrap css -->
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
	
	<!-- JQuery js -->
	<script src="https://code.jquery.com/jquery-2.1.4.js"></script>
	
	<!-- Bootstrap toggle css/js -->
	<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.0/css/bootstrap-toggle.min.css" rel="stylesheet">
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.0/js/bootstrap-toggle.min.js"></script>
    
	<script src="javascript/toggleAdvancedSearch.js"></script>
</head>

<body>
	<div class="container" style="position:absolute; top: 35%; width:100%; margin:auto; color:Black;" align="center">
		<h1>MU RESEARCH</h1>
		<form action="result.php" method="POST">
			<input type="text" class="form-control" name ="queryString" align="center" style="height:35px;width:500px;border-radius:4px;" required autofocus>
			<br>
			Advance Search:  <input id="advancedSearchToggle" type="checkbox" data-toggle="toggle">
			<br><br>
			<div id="advanced_search_content">
				<div id="datasources">
					<input type="checkbox" name="datasources[]" value="mospace"> MOspace
					<input type="checkbox" name="datasources[]" value="ieee"> IEEE
					<input type="checkbox" name="datasources[]" value="pubmed"> PubMed
					<!--<input type="checkbox" name="datasources[]" value="googleScholar" disabled> Google Scholar-->
					<input type="checkbox" name="datasources[]" value="news"> News
					<input type="checkbox" name="datasources[]" value="events"> Events
				</div>
				<div style="position: relative; margin: auto">
					Maximum return records:
					<select id="num_of_records", name="num_of_records">
						<option value="5">5</option>
						<option value="10" selected="selected">10</option>
						<option value="20">20</option>
						<option value="30">30</option>
						<option value="50">50</option>
					</select>
				</div>
			</div>
			<br><br>
			<button type="submit" name="submit_search" class="btn btn-primary">Search <span class="glyphicon glyphicon-search"></span></button>
		</form>
	</div>
</body>
</html>
