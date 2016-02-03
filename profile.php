<?php

include_once "databaseConfig.php";
include_once "FixCertificate.php";
include_once "vendor/autoload.php";

$registered = 0;
global $context;

if(isset($_GET['id']) && !empty($_GET['id'])){
    $id = $_GET['id'];
}
else if(isset($_GET['name']) && !empty($_GET['name'])){
    $name = urldecode($_GET['name']);
    $parser = new HumanNameParser_Parser($name);

}
else{
    exit("error: either an id or a name should be specified");
}


$mysqli = new mysqli(HOSTNAME, USERNAME, PASSWD, DATABASE);
if($mysqli->connect_errno){
    exit("error:". $mysqli->connect_error);
}

if(isset($id)){
    $query = "SELECT * from registeredUser where id = ? ";
}
else if(isset($name)){
    $query = "SELECT * from registeredUser where lastname = ? and firstname = ? and middlename = ? and suffix = ?";
}
else{
    exit("FATAL ERROR: should not get this far");
}

if(!($stmt = $mysqli->prepare($query))){
    exit("Prepared failed: (". $mysqli->errno . ")". $mysqli->error);
}

if(isset($id)){
    if(!$stmt->bind_param("i", $id)){
        exit("Binding parameters failed: (". $stmt->errno. ")". $stmt->error);
    }

    if(!$stmt->execute()){
        exit("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    }

    $stmt->store_result();

    if($stmt->num_rows == 0 || $stmt->num_rows >= 2){
        exit("Error: id not found or multiple result found");
    }

    $stmt->bind_result($userid, $lastname, $firstname, $middlename, $suffix, $title, $department, $phone, $email, $photoImageURL, $keywordImageURL, $socialKeywordURL, $description);
    $stmt->fetch();
    $registered = 1;
}
else if(isset($name)){


    $bind_param_last_name = $parser -> getLast();
    $bind_param_first_name = $parser -> getFirst();
    $bind_param_middle_name = $parser -> getMiddle();
    $bind_param_suffix = $parser -> getSuffix();

    if(!$stmt->bind_param("ssss", $bind_param_last_name, $bind_param_first_name, $bind_param_middle_name, $bind_param_suffix)){

        exit("Binding parameters failed: (". $stmt->errno. ")". $stmt->error);
    }

    if(!$stmt->execute()){
        exit("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    }

    $stmt->store_result();

    if($stmt->num_rows == 1){
        $stmt->bind_result($theid, $lastname, $firstname, $middlename, $suffix, $title, $department, $phone, $email, $photoImageURL, $keywordImageURL, $socialKeywordURL, $description);
        $stmt->fetch();
        $registered = 1;
    }
    else if($stmt->num_rows > 1){
        exit("Bad luck: Multiple records match");
    }
    else if($stmt->num_rows == 0){
        $lastname = $parser->getLast();
        $firstname = $parser->getFirst();
        $middlename = $parser->getMiddle();
        $suffix = $parser->getSuffix();

        $peopleFinderURL = "https://webservices.doit.missouri.edu/peoplefinderWS/peoplefinderws.asmx/PeopleFinderXml?firstName=".urlencode($firstname)."&lastname=".urlencode($lastname)."&department=&phoneno=&email=";
        //exit($peopleFinderURL);
        $people_xml = file_get_contents($peopleFinderURL, false, $context);
        $people_string_array = simplexml_load_string($people_xml, null, LIBXML_NOCDATA);
        $people_json_array = json_decode(json_encode($people_string_array), TRUE);

        if(intval($people_json_array['@attributes']['found']) == 1){


            $department = (array_key_exists("Department", $people_json_array['Person']) && !empty($people_json_array['Person']['Department'])) ? $people_json_array['Person']['Department'] : "";
            $title = (array_key_exists("Title", $people_json_array['Person']) && !empty($people_json_array['Person']['Title'])) ? $people_json_array['Person']['Title'] : "";
            $phone = (array_key_exists("Phone", $people_json_array['Person']) && !empty($people_json_array['Person']['Phone'])) ? $people_json_array['Person']['Phone'] : "";
            $email = (array_key_exists("E-mail", $people_json_array['Person']) && !empty($people_json_array['Person']['E-mail'])) ? $people_json_array['Person']['E-mail'] : "";
        }
    }
}


if(isset($_POST['submit_photo'])){
	
	$url_root = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/profile.php';

	if(isset($_GET['status']) && !empty($_GET['status'])){
		$home_url = $url_root.'?name='.$_GET['name'].'&status='.$_GET['status'];
	}
	else{
		$home_url = $url_root.'?name='.$_GET['name'];
	}

	$dir='/user_photo/';
	list($width,$height,$type,$attr) = getimagesize($_FILES['photo']['tmp_name']);

	switch($type){
		case IMAGETYPE_GIF:
			$image = imagecreatefromgif($_FILES["photo"]['tmp_name']) or die('The file you upload was not supported filetype');
			$ext = '.gif';
			break;
		case IMAGETYPE_JPEG:
			$image = imagecreatefromjpeg($_FILES["photo"]['tmp_name']) or die('The file you upload was not supported filetype');
			$ext = '.jpg';
			break;    
		case IMAGETYPE_PNG:
			$image = imagecreatefrompng($_FILES["photo"]['tmp_name']) or die('The file you upload was not supported filetype');
			$ext = '.png';
			break;    
		default    :
			die('The file you uploaded was not a supported filetype.');
	}
	$imagename = $theid.$ext;

	//echo '<script>alert("'.$imagename.'")</script>';
	//echo '<script>alert("'.$width.'")</script>';

	switch($type){
    	case IMAGETYPE_GIF:
			imagegif($image,'.'.$dir.$imagename);
			break;
		case IMAGETYPE_JPEG:
			imagejpeg($image,'.'.$dir.$imagename);
			break;
		case IMAGETYPE_PNG:
			imagepng($image,'.'.$dir.$imagename);
			break;
	}
	$url='http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']).$dir.$imagename;
	$query="UPDATE registeredUser SET photoImageURL='$url' WHERE id=$theid";
	mysqli_query($mysqli, $query);
	
	header('Location: ' . $home_url);
}
$stmt->close();
$mysqli->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>MU Research</title>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>

    <!-- CSS customizations -->
    <link href="css/profile.css" rel="stylesheet" />
    <link href="css/profile_page.css" rel="stylesheet" />
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
	<link href="css/style.css" rel="stylesheet" />

    
    <script src="javascript/cytoscape.js"></script>
    <script src="javascript/jquery.blockUI.js"></script>
    <script src="javascript/network.js"></script>

	<script src="javascript/jquery.validate.min.js"></script>
    <script src="javascript/profile_page.js"></script>
</head>

<body style="margin-left: 10%; margin-right: 10%">

    <div class="row row-profile">
        <div class="header">
            <div style="padding: 10px; text-align: center">
                <a href="http://muresearch.missouri.edu"><strong style="color:white; position: relative; font-size: 25px">MU RESEARCH</strong></a>
            </div>
        </div>
    </div>

    <div class="row">
        <h1>Research Profile</h1>
    </div>
	<?php 
	if(isset($_GET['status']) && $_GET['status']=='edit' && isset($registered) && $registered==1){
		echo '<div id="profile-overview" onMouseOver="return show_icon();">';
	}else{
		echo '<div>';
	}?>
		<div class="row section row-profile" style="margin-top:5px;">
			<h3 class="section-header">
				<?php
				echo ucfirst($lastname).", ". ucfirst($firstname). " ". $middlename. " ". ucfirst($suffix);
				?>
			</h3>
			<div class='row' id="profile-block">
				<!--user photo-->
				<div class='col-md-3' id="left-panel">
					<div class="col-md-12" id="profile-photo">
						<img id="field_photo" src="<?php if($registered == 1){echo $photoImageURL;} else{echo "http://freelanceme.net/Images/default%20profile%20picture.png";} ?>" alt="Profile Image" style='width:100%' />
						<span onclick="return show_upload();">
							Change Photo
						</span>
					</div>
					<div id="upload-photo">
						<button class="close-btn" onclick="return hide_upload();">
							<i class="material-icons">close</i>
						</button>
					<form id="upload-photo-form" method="POST" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'].'?name='.$_GET['name'].'&status='.$_GET['status'];?>">
						<label class="field-title">Upload a new image: </label ><br/>
						<input class="upload-input" id="field-photo" type="file" required name="photo" value="Browse..." style="margin:10px;" accept="image/*"/>
						<!--<div id="upload_btns">-->
						<!--<button type="button" name="submit_photo" class="submit-btn">Upload</button>-->
							<div><input type="submit" name="submit_photo" class="submit-btn" value="Upload"/></div>&nbsp;
							<!--<button type="button" onclick="hide_upload()" class="cancel-btn">Cancel</button>-->
						<!--</div>-->
					</form><br/>
					<span style="font-style:italic;">OR</span><br/>
					<form id="upload-photo-url-form" method="POST" enctype="multipart/form-data" action="">
						<div class="field-title">Use an image URL: </div><br/>
						<div><input class="upload-input" id="field-photo-url" type="url" required name="photo_url" style="margin:10px;" value="http://" accept="image/*"/></div>
						<!--<div id="upload_btns">-->
						<!--<button type="button" name="submit_photo" class="submit-btn">Upload</button>-->
							<div><input type="submit" name="submit_photo_url" class="submit-btn" value="Submit" style="display:inline;"/></div>&nbsp;
						<input class="hidden_input" name="is_photo_url" value="1"/>
						<input class="hidden_input" name="user_id" value="<?php echo $theid;?>"/>
							<!--<button type="button" onclick="hide_upload()" class="cancel-btn">Cancel</button>-->
						<!--</div>-->
					</form>
					</div>
				</div>
				<div class="col-md-9" style="padding:0;">
					<?php
					if(isset($title) && !empty($title)){?>
						<div class="field">
							<div class="field-content">
								<div id="field_title"><?php echo $title ?></div>
								<button class="edit-btn" onclick="return show_dialog(0);">
									<i class="material-icons">edit</i>
								</button>
								<!-- action="<?php echo $_SERVER['PHP_SELF'].'?name='.$_GET['name'];?>"-->
								<form class="edit-zone" id="title" method="POST" name="update_title" onsubmit="return false;">
									<input class="input" name="title" required value="<?php echo $title;?>"/>
									<input class="hidden_input" name="is_title" value="1"/>
									<input class="hidden_input" name="user_id" value="<?php echo $theid;?>"/>
									<input type="submit" name="submit_title" class="submit-btn" value="Save">
									<button type="button" onclick="hide_dialog()" class="cancel-btn">Cancel</button>
									<span class="edit-zone-triangle"></span>
								</form>
								<script>$("#title").validate();</script>
							</div>
						</div>
						<br/>
					<?php }?>

					<?php
					if(isset($phone) && is_string($phone)){?>
						<div class="field">
							<div class="field-title">Phone: </div>
							<div class="field-content">
								<span id="field_phone"><?php echo $phone;?></span>
								<button class="edit-btn" onclick="return show_dialog(1);">
									<i class="material-icons">edit</i>
								</button>
								<form class="edit-zone" id="phone" method="POST" name="update_phone" onsubmit="return false;">
									<input class="input" name="phone" required id="phone_input" value="<?php echo $phone;?>"/>
									<input class="hidden_input" name="is_phone" value="1"/>
									<input class="hidden_input" name="user_id" value="<?php echo $theid;?>"/>
									<input type="submit" name="submit_photo" class="submit-btn" value="Save">
									<button type="button" onclick="hide_dialog()" class="cancel-btn">Cancel</button>
									<span class="edit-zone-triangle"></span>
								</form>
								<script>$("#phone").validate();</script>
							</div>
						</div>
					<?php }?>

					<?php
					if(isset($email) && is_string($email)){?>
						<div class="field">
							<div class="field-title">Email: </div>
							<div class="field-content">
								<span id="field_email"><?php echo $email;?></span>
								<button class="edit-btn" onclick="return show_dialog(2);">
									<i class="material-icons">edit</i>
								</button>
								<form class="edit-zone" id="email" method="POST" name="update-email" onsubmit="return false;">
									<input class="input" name="email" type="email" required value="<?php echo $email;?>"/>
									<input class="hidden_input" name="is_email" value="1"/>
									<input class="hidden_input" name="user_id" value="<?php echo $theid;?>"/>
									<input type="submit" name="submit_email" class="submit-btn" value="Save">
									<button type="button" onclick="hide_dialog()" class="cancel-btn">Cancel</button>
									<span class="edit-zone-triangle"></span>
								</form>
								<script>$("#email").validate();</script>
							</div>
						</div>
					<?php }?>

					<?php
					if(isset($department) && is_string($department)) {?>
						<div class="field">
							<div class="field-title">Department: </div>
							<div class="field-content">
								<span id="field_department"><?php echo $department;?></span>
								<button class="edit-btn" onclick="return show_dialog(3);">
									<i class="material-icons">edit</i>
								</button>
								<form class="edit-zone" id="department" method="POST" name="update-department" onsubmit="return false;">
									<input class="input" name="department" required value="<?php echo $department;?>"/>
									<input class="hidden_input" name="is_department" value="1"/>
									<input class="hidden_input" name="user_id" value="<?php echo $theid;?>"/>
									<input type="submit" name="submit_department" class="submit-btn" value="Save">
									<button type="button" onclick="hide_dialog()" class="cancel-btn">Cancel</button>
									<span class="edit-zone-triangle"></span>
								</form>
								<script>$("#department").validate();</script>
							</div>
						</div>
					<?php }?>
				</div>
			</div>
		</div>

	<?php
	if($registered == 1){
	?>
		<div class="row section row-profile">
			<h3 class='section-header'>Overview
				<button class="edit-btn" onclick="return show_dialog(4);">
					<i class="material-icons">edit</i>
				</button>
			</h3>
			<div class='row' id="profile-text">
				<div class="field-content" id="description">
					<div id="description_div">
						<?php echo iconv("ISO-8859-1", "UTF-8//IGNORE", $description);?>
					</div>
					<form method="POST" id="description_form" class="edit-zone" name="update_description" onsubmit="return false;">
						<textarea id="description_textarea" name="description"><?php echo iconv("ISO-8859-1", "UTF-8//IGNORE", $description);?></textarea>

						<div id="description-btns">
							<input class="hidden_input" name="is_overview" value="1"/>
							<input class="hidden_input" name="user_id" value="<?php echo $theid;?>"/>
							<input type="submit" name="submit_description" class="submit-btn" value="Save">
							<button type="button" onclick="return disable_description();" class="cancel-btn">Cancel</button>
						</div>
						<script src="javascript/jquery.flexText.min.js"></script>
						<script>
							$(function () {
								$('#description_textarea').flexText();
								$('.flex-text-wrap').css('display','none');
							});
						</script>
					</form>
				</div>	
			</div>
		</div>
	<?php }?>
	</div>	
</div>

<br><br>

<div class="row">
    <div style="width:100%; height: 700px; border-style: solid">
        <div style="width: 100%; height: 5%">
            <button id="center">Center</button>
			<span>
				<button id="circleLayout">Circle</button>
				<button id="concentricLayout">Concentric</button>
				<button id="randomLayout">Random</button>
				<button id="bfLayout">BreadthFirst</button>
				<button id="gridLayout">Grid</button>
				<button id="onlyProfessor">Show/Hide Students</button>
			</span>
        </div>
        <div id="cy" style="width: 100%; height: 95%"></div>
    </div>
</div>

<?php

//This name format must be consistent with neo4j name format: <Last>, <First>
$unifiedName = $parser->getLast().", ".$parser->getFirst();

?>


<script>

    $(function(){

        var unifiedName = "<?php echo $unifiedName; ?>";
        var non_professor_eles = null;

        var cy = cytoscape({
            container: document.getElementById('cy'),

            style: [
                {
                    selector: 'node',
                    style: {
                        'content': 'data(name)',
                        'width': 'data(nodeSize)',
                        'height': 'data(nodeSize)'
                    }
                },

                {
                    selector: "edge",
                    style: {
                        'width': 'data(edgeSize)',
                        'line-color': 'mapData(edgeSize, 0, 10, #e0e0e0, black)'
                    }
                }
            ],

            layout: {
                name: 'grid',
                //animate: true,
                //animationDuration: 1000
            },

            ready: function(){

                var non_professor_edgeEles;

                $('#center').on('click', function(){
                    var j = cy.elements("node[name='" + unifiedName + "']");
                    cy.center(j);
                    //cy.reset();
                });

                $('#circleLayout').on('click', function(){
                    var layout = cy.makeLayout({
                        name: 'circle'
                    });

                    layout.run();
                });

                $('#concentricLayout').on('click', function(){
                    var layout = cy.makeLayout({
                        name: 'concentric'
                    });

                    layout.run();
                });

                $('#randomLayout').on('click', function(){
                    var layout = cy.makeLayout({
                        name: 'random'
                    });

                    layout.run();
                });

                $('#gridLayout').on('click', function(){
                    var layout = cy.makeLayout({
                        name: 'grid'
                    });

                    layout.run();
                });

                $('#bfLayout').on('click', function(){
                    var layout = cy.makeLayout({
                        name: 'breadthfirst'
                    });

                    layout.run();
                });

                $('#onlyProfessor').on('click', function(){
                    if(non_professor_eles == null){
                        //console.log(unifiedName);
                        non_professor_eles = cy.elements("node[isProfessor = 0][name != '" + unifiedName + "']");
                        non_professor_edgeEles = non_professor_eles.neighborhood('edge');
                    }

                    //console.log(non_professor_edgeEles);
                    if(non_professor_eles.removed()){
                        non_professor_eles.restore();
                        non_professor_edgeEles.restore();
                    }
                    else{
                        non_professor_eles.remove();
                    }
                });

                cy.on('click', 'node', function(evt){
                    var node = evt.cyTarget;
                    var unifiedName = node.data('name');

                    location.href = "http://muresearch.missouri.edu/profile.php?name="+unifiedName;
                });

                $.when($.ajax({
                    url: 'enlargeNeoDatabase.php',
                    type: 'POST',
                    data: {data: JSON.stringify({'name': unifiedName})},
                    success: function(data){
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
                })).done(function(){
                    loadNetwork(unifiedName, cy);
                    loadPublication(unifiedName);
                });

            }
        });

        $(document).ajaxSend(function(event, xhr, settings){
            if(settings.url == "getCytoscapeJSON.php" || settings.url == "enlargeNeoDatabase.php"){
                $('div#cy').block({ message: '<h1>This process will take long...</h1>',
                    css: {
                        border: 'none',
                        padding: '15px',
                        backgroundColor: '#000',
                        '-webkit-border-radius': '10px',
                        '-moz-border-radius': '10px',
                        opacity: .5,
                        color: '#fff'
                    }
                });
            }
        });


        $(document).ajaxComplete(function(event, xhr, settings){
            if(settings.url == "getCytoscapeJSON.php" || settings.url == "enlargeNeoDatabase.php") {
                $('div#cy').unblock();
            }
        });
    })

</script>


<?php
//if(isset($registered) && $registered==1){
   // echo '<div class=\'row section\' id="publication-block" onMouseOver="show_icon()">';
//}else{
    //echo '<div class=\'row section\' id="publication-block">';
//}
?>
<div class='row section' id="publication-block" style="margin-top:5px;">
    <h3 class='section-header' style="margin-bottom:14px;">Publications
		<?php
		if(isset($_GET['status']) && $_GET['status']=='edit'){
		?>
			<button class="add-btn" onclick="return show_dialog(5);" style="visibility:visible;">
				<i class="material-icons">add</i>
			</button>
		<?php
		}
		?>	
    </h3>

    <form class="publication-add-zone form-horizontal" id="publication" method="POST" name="add_publication">

        <div class="form-group">
            <label class="col-md-1 control-label">Title</label>
            <div class="col-md-10">
                <input type="text" class="form-control" name="publication_title" required/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-1 control-label">URL</label>
            <div class="col-md-10">
                <input type="url" class="form-control" name="publication_url" value="http://" required/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-1 control-label">Author</label>
            <div class="col-md-2">
                <input type="text" class="form-control" name="author[0].firstname" value="<?php echo $firstname ?>" disabled />
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control" name="author[0].lastname" value="<?php echo $lastname ?>" disabled />
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control" name="author[0].affiliation" placeholder="Affiliation" />
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-default addButton" aria-label="add author">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </button>
            </div>
        </div>


        <!-- The template for adding new field -->
        <div class="form-group hide" id="AuthorTemplate">
            <div class="col-md-2 col-md-offset-1">
                <input type="text" class="form-control" name="firstname" required placeholder="FirstName" />
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control" name="lastname" required placeholder="LastName" />
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control" name="affiliation" placeholder="Affiliation" />
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-default removeButton">
                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                </button>
            </div>
        </div>

        <div class="col-md-5 col-md-offset-1">
            <input type="submit" name="submit_publication" id="add-publication-btn" class="submit-btn" value="Submit" />
            <input type="hidden" id="unifiedname-of-current-person" value="<?php echo $unifiedName ?>"/>
            <button type="button" onclick="hide_dialog()" class="cancel-btn">Cancel</button>
        </div>
    </form>
	<script>$("#publication").validate();</script>
	<div class="hint-success" id="hint-success-publication">
		The publication is added
	</div>
    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    </div>
</div>
</div>

</body>
</html>
