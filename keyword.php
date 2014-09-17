<?php
	/*
		Keyword Search
		Western Washington University
		Service Learning Center Database
	*/
	include('database.php');
	include_once('CAS.php');
	phpCAS::client(CAS_VERSION_2_0, 'websso.wwu.edu', 443, '/cas');
	phpCAS::setNoCasServerValidation();
	if (!phpCAS::isAuthenticated()) phpCAS::forceAuthentication();
	if (isset($_REQUEST['logout'])) phpCAS::logout();
	if (phpCAS::isAuthenticated()) $casuser = phpCAS::getUser();
?>

<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<meta content="" name="description">
	<meta content="" name="author">
	<link href="" rel="shortcut icon">
	<title>Keyword Search</title>
	<link href="bootstrap.css" rel="stylesheet">
</head>
<body>
	<img border="0" src="banner.jpg" width="100%" height="150">
	<link href="bootstrap.css" rel="stylesheet">
	<!Navigation bar>
	<ul class="nav nav-pills">
		<li><a href="main.php">Home</a></li>
		<li><a href="info.php">Info</a></li>
		<li><a href="add.php">Add</a></li>
		<li><a href="report.php">Report</a></li>
		<li><a href="course.php">Course</a></li>
		<li><a href="section.php">Section</a></li>
		<li><a href="faculty.php">Faculty</a></li>
		<li><a href="partner.php">Partner</a></li>
		<li><a href="project.php">Project</a></li>
	<?php 
	
	if (!phpCAS::isAuthenticated())
	{
		echo '<li><a href="login.php">Login</li></a>';
	}
	else
	if(phpCAS::isAuthenticated())
	{
		echo '	<li><a>You are logged in as <font color="red">' . $casuser . '</font></li></a>
					<li><a href="?logout">(Logout)</li></a>';
	}

	?>
		<li>
			<form action="keyword.php" method=POST>
				<input type=text align="center" style="width: 25em" name="keyword" placeholder="Search...">
				<input type="submit" name="ksearch" value="Search">
				<br>
				<font color="white">
				<input type="checkbox" name="searchTables[]" value="section" checked>Section
				<input type="checkbox" name="searchTables[]" value="faculty" checked>Faculty
				<input type="checkbox" name="searchTables[]" value="project" checked>Projects
				<input type="checkbox" name="searchTables[]" value="partner" checked>Partners
				<input type="checkbox" name="includeComments" value="Yes" checked>Include Comments
				</font>
			</form>
		</li>
</ul>
<?php
	$host = "db.cs.wwu.edu";
	$dbname = "db_slc1410";
	$username = "mille362_writer";
	$password = "HerNDmGk82";
	$database = new Database($host, $username, $password, $dbname);
	
	if ($database->connect_errno)
	{
		echo "Failed to connect to MySQL: (" . $database->connect_errno . ") " . $database->connect_error;
	}
	
	/*	Function keywordSearch
	 *	Given a database and string, searches attributes in the database for the matching string
	 *	If a match is found, prints the row of the matched attribute
	 *
	 *	Parameters:
			$database: the database to be searched
			$string: the input string
			$tables: an array of strings, the table names to be searched
	 */
	function keywordSearch ($database, $string, $tables)
	{
		//Tokenize $string by spaces
		$stringTokens = explode(" ", $string);
		
		if (in_array("section", $tables)){
			//keyword search section
			
			//If include comments box is checked, search the comments
			if ($_POST["includeComments"]){
				$query = "SELECT * FROM section LEFT JOIN course ON course.courseID = section.sectionFK_courseID LEFT JOIN sectionComments ON section.sectionID = sectionComments.comFK_sectionID;";
			}else{
				$query = "SELECT * FROM section LEFT JOIN course ON course.courseID = section.sectionFK_courseID;";
			}
			
			$searchFields = array("sectionCRN", "sectionQuarter", "sectionYear", "sectionCreator", "sectionCom", "courseNum", "courseName", "courseDept");
			//$query = "SELECT * FROM section;";
			$qry =  $database->multi_query($query) or die("Error: " . mysqli_error( $database) . "<br>");
			
			
			
			
			if($result =  $database->use_result())
			{
				$printHead = False;
				$printedResult = False;
				
				$exportRows = array();
				$rowIndex = 0;
				while ($row = mysqli_fetch_array($result))
				{	
					$rowPrinted = False;
					
					foreach ($searchFields as &$field)
					{
						//Search row for each token of input
						foreach ($stringTokens as &$token)
						{
							//Added strtolower function to remove case sensitivity
							if (strpos(strtolower($row[$field]), strtolower($token)) !== False)
							{
								if ($printHead == False)
								{
									echo '<h4>Section Results</h4>';
									echo '<table class="table table-striped" style="width:50%">';
									echo '<thead><tr>';
									echo '<th>Last Modified</th>';
									echo '<th>Modified By</th>';
									echo '<th>CRN</th>';
									echo '<th>Course</th>';
									echo '<th>Course Name</th>';
									echo '<th>Quarter</th>';
									echo '<th>Year</th>';
									echo '<th>Comments</th>';
									echo '<th>Details</th>';
									echo '</tr></thead>';
									echo '<tbody>';
									$printHead = True;
								}

								echo '<tr>';
								echo '<td>' . $row["sectionLastMod"] . '</td>';
								echo '<td>' . $row["sectionCreator"] . '</td>';
								echo '<td>' . $row["sectionCRN"] . '</td>';
								echo '<td>' . $row["courseDept"].$row["courseNum"] . '</td>';
								echo '<td>' . $row["courseName"] . '</td>';
								echo '<td>' . $row["sectionQuarter"] . '</td>';
								echo '<td>' . $row["sectionYear"] . '</td>';
								
								if (strpos($row["sectionCom"], $token) !== False)
								{
									echo '<td>' . $row["sectionCom"] . '</td>';
								}
								else
								{
									echo '<td></td>';
								}

								echo '<form action="section.php" method="POST">';
								echo '<td>';
								echo '<input type="hidden" name="sectionID" value="'. $row["sectionID"] .'">';
								echo '<input type="submit" name="profile" value="Details">';
								echo '</td>';
								echo '</form>';
								echo '</tr>';
								
								array_push($exportRows, strval($rowIndex));
								$rowPrinted = True;
								$printedResult = True;
								break;
								
							}
						}
						if ($rowPrinted){
							break;
						}
					}
					$rowIndex = $rowIndex+1;
				}
				echo '</tbody>';
				echo '</table>';
				
				if ($printedResult){
					echo '<form action="export.php" method="POST">';
					echo '<input type="hidden" name="export" value="'.$query.'">';
					//echo '<input type="hidden" name="export" value="SELECT * FROM section LEFT JOIN course ON course.courseID = section.sectionFK_courseID">';
					echo '<input type="hidden" name="outname" value="section">';
					echo '<input type="hidden" name="exportKeyword" value="exportKeyword">';
					foreach ($exportRows as &$index){
						echo '<input type="hidden" name="exportRows[]" value="'.$index.'">';
					}
					echo '<input type="submit" value="Export">';
					echo '</form>';
				}
			}
		}
		
		if (in_array("faculty", $tables)){
			//keyword search faculty
			
			//If include comments is checked, change the query
			if ($_POST["includeComments"]){
				$query = "SELECT * FROM faculty LEFT JOIN facultyComments ON faculty.facultyID = facultyComments.comFK_facultyID;";
			}else{
				$query = "SELECT * FROM faculty";
			}
			$searchFields = array("facultyName", "facultyDept", "facultyDesc", "facultyCreator", "facultyCom");
			$qry =  $database->multi_query($query) or die("Error: " . mysqli_error( $database) . "<br>");
			
			if($result =  $database->use_result())
			{
				$printedResult = False;
				$printHead = False;
				
				$exportRows = array();
				$rowIndex = 0;
				while ($row = mysqli_fetch_array($result))
				{
					$rowPrinted = False;
					foreach ($searchFields as &$field)
					{
						//Search row for each token of input
						foreach ($stringTokens as &$token)
						{
							//Added strtolower function to remove case sensitivity
							if (strpos(strtolower($row[$field]), strtolower($token)) !== False)
							{
								
								if ($printHead == False)
								{
									echo '<h4>Faculty Results</h4>';
									echo '<table class="table table-striped" style="width:50%">';
									echo '<thead><tr>';
									echo '<th>Last Modified</th>';
									echo '<th>Modified By</th>';
									echo '<th>Name</th>';
									echo '<th>Department</th>';
									echo '<th>Trained?</th>';
									echo '<th>Description</th>';
									echo '<th>Comments</th>';
									echo '<th>Details</th>';
									echo '</tr></thead>';
									echo '<tbody>';
									$printHead = True;
								}

								echo '<tr>';
								echo '<td>' . $row["facultyLastMod"] . '</td>';
								echo '<td>' . $row["facultyCreator"] . '</td>';
								echo '<td>' . $row["facultyName"] . '</td>';
								echo '<td>' . $row["facultyDept"] . '</td>';
								echo '<td>' . ($row["facultyTraining"] == 1 ? "Yes" :
								"No"). '</td>';
								
								if (strpos($row["facultyDesc"], $token) !== False)
								{
									echo '<td>' . $row["facultyDesc"] . '</td>';
								}
								else
								{
									echo '<td></td>';
								}

								
								if (strpos($row["facultyCom"], $string) !== False)
								{
									echo '<td>' . $row["facultyCom"] . '</td>';
								}
								else
								{
									echo '<td></td>';
								}

								echo '<form action="faculty.php" method="POST">';
								echo '<td>';
								echo '<input type="hidden" name="facultyID" value="'. $row["facultyID"] .'">';
								echo '<input type="submit" name="profile" value="Details">';
								echo '</td>';
								echo '</form>';
								echo '</tr>';
								
								array_push($exportRows, strval($rowIndex));
								$rowPrinted = True;
								$printedResult = True;
								break;
							}
						}
						if ($rowPrinted)
						{
							break;
						}
					}
					$rowIndex = $rowIndex+1;
				}
				echo '</tbody>';
				echo '</table>';
				
				if ($printedResult){
					echo '<form action="export.php" method="POST">';
					echo '<input type="hidden" name="export" value="'.$query.'">';
					echo '<input type="hidden" name="outname" value="faculty">';
					echo '<input type="hidden" name="exportKeyword" value="exportKeyword">';
					foreach ($exportRows as &$index){
						echo '<input type="hidden" name="exportRows[]" value="'.$index.'">';
					}
					
					echo '<input type="submit" value="Export">';
					echo '</form>';
				}
			}
		}
		
		if (in_array("project", $tables)){

			//keyword search project
			
			//If include comments is checked, change the query
			if ($_POST["includeComments"]){
				$query = "SELECT * FROM project LEFT JOIN projectComments ON project.projectID = projectComments.comFK_projectID;";
			}else{
				$query = "SELECT * FROM project";
			}
			$query = "SELECT * FROM project LEFT JOIN projectComments ON project.projectID = projectComments.comFK_projectID;";
			$searchFields = array("projectName", "projectType", "projectStatus", "projectDesc", "projectCreator", "projectCom", "partnerName");
			$qry =  $database->multi_query($query) or die("Error: " . mysqli_error( $database) . "<br>");
			
			if($result =  $database->use_result())
			{
				$printedResult = False;
				$printHead = False;
				
				$exportRows = array();
				$rowIndex = 0;
				while ($row = mysqli_fetch_array($result))
				{
					$rowPrinted = False;
					foreach ($searchFields as &$field)
					{
						//Search row for each token of input
						foreach ($stringTokens as &$token)
						{
							//Added strtolower function to remove case sensitivity
							if (strpos(strtolower($row[$field]), strtolower($string)) !== False)
							{
								
								if ($printHead == False)
								{
									echo '<h4>Project Results</h4>';
									echo '<table class="table table-striped" style="width:50%">';
									echo '<thead><tr>';
									echo '<th>Last Modified</th>';
									echo '<th>Modified By</th>';
									echo '<th>Name</th>';
									echo '<th>Type</th>';
									echo '<th>Status</th>';
									echo '<th>Comments</th>';
									echo '<th>Details</th>';
									echo '</tr></thead>';
									echo '<tbody>';
									$printHead = True;
								}

								echo '<tr>';
								echo '<td>' . $row["projectLastMod"] . '</td>';
								echo '<td>' . $row["projectCreator"] . '</td>';
								echo '<td>' . $row["projectName"] . '</td>';
								echo '<td>' . $row["projectType"] . '</td>';
								echo '<td>' . $row["projectStatus"] . '</td>';
								
								if (strpos($row["projectCom"], $string) !== False)
								{
									echo '<td>' . $row["projectCom"] . '</td>';
								}
								else
								{
									echo '<td></td>';
								}

								echo '<form action="project.php" method="POST">';
								echo '<td>';
								echo '<input type="hidden" name="projectID" value="'.$row["projectID"].'">';
								echo '<input type="submit" name="profile" value="Details">';
								echo '</td>';
								echo '</form>';
								echo '</tr>';
								
								
								array_push($exportRows, strval($rowIndex));
								$rowPrinted = True;
								$printedResult = True;
								break;
							}
						}
						if ($rowPrinted)
						{
							break;
						}
					}
					$rowIndex = $rowIndex+1;
				}
				echo '</tbody>';
				echo '</table>';
				
				if ($printedResult){
					echo '<form action="export.php" method="POST">';
					echo '<input type="hidden" name="export" value="'.$query.'">';
					echo '<input type="hidden" name="outname" value="project">';
					echo '<input type="hidden" name="exportKeyword" value="exportKeyword">';
					foreach ($exportRows as &$index){
						echo '<input type="hidden" name="exportRows[]" value="'.$index.'">';
					}
					echo '<input type="submit" value="Export">';
					echo '</form>';
				}
			}
		}
		
		if (in_array("partner", $tables)){
			//keyword search partner
			
			//If include comments is checked, change the query
			if ($_POST["includeComments"]){
				$query = "SELECT * FROM partner LEFT JOIN partnerComments ON partner.partnerID = partnerComments.comFK_partnerID;";
			}else{
				$query = "SELECT * FROM partner";
			}
			$searchFields = array("partnerName", "partnerContact", "partnerType", "partnerDesc", "partnerCreator", "partnerCom");
			$qry =  $database->multi_query($query) or die("Error: " . mysqli_error( $database) . "<br>");
			
			if($result =  $database->use_result())
			{
				$printHead = False;
				$printedResult = False;
				
				$exportRows = array();
				$rowIndex = 0;
				while ($row = mysqli_fetch_array($result))
				{
					$rowPrinted = False;
					foreach ($searchFields as &$field)
					{
						//Search row for each token of input
						foreach ($stringTokens as &$token)
						{
							if (strpos(strtolower($row[$field]), strtolower($string)) !== False)
							{
								
								if ($printHead == False)
								{
									echo '<h4>Partner Results</h4>';
									echo '<table class="table table-striped" style="width:50%">';
									echo '<thead><tr>';
									echo '<th>Last Modified</th>';
									echo '<th>Modified By</th>';
									echo '<th>Name</th>';
									echo '<th>Contact</th>';
									echo '<th>Type</th>';
									echo '<th>Comments</th>';
									echo '<th>Details</th>';
									echo '</tr></thead>';
									echo '<tbody>';
									$printHead = True;
								}

								echo '<tr>';
								echo '<td>' . $row["partnerLastMod"] . '</td>';
								echo '<td>' . $row["partnerCreator"] . '</td>';
								echo '<td>' . $row["partnerName"] . '</td>';
								echo '<td>' . $row["partnerContact"] . '</td>';
								echo '<td>' . $row["partnerType"] . '</td>';
								
								if (strpos($row["partnerCom"], $string) !== False)
								{
									echo '<td>' . $row["partnerCom"] . '</td>';
								}
								else
								{
									echo '<td></td>';
								}

								echo '<form action="partner.php" method="POST">';
								echo '<td>';
								echo '<input type="hidden" name="partnerID" value="'.$row["partnerID"].'">';
								echo '<input type="submit" name="profile" value="Details">';
								echo '</td>';
								echo '</form>';
								echo '</tr>';
								
								array_push($exportRows, strval($rowIndex));
								$rowPrinted = True;
								$printedResult = True;
								break;
							}
						}
						if ($rowPrinted)
						{
							break;
						}
					}
					$rowIndex = $rowIndex+1;

				}
				echo '</tbody>';
				echo '</table>';
				
				if ($printedResult){
					echo '<form action="export.php" method="POST">';
					echo '<input type="hidden" name="export" value="'.$query.'">';
					echo '<input type="hidden" name="outname" value="partner">';
					echo '<input type="hidden" name="exportKeyword" value="exportKeyword">';
					foreach ($exportRows as &$index){
						echo '<input type="hidden" name="exportRows[]" value="'.$index.'">';
					}
					echo '<input type="submit" value="Export">';
					echo '</form>';
				}
			}
		}
	}
	
	if ($_POST["keyword"])
	{
	
		//If no tables were selected in POST
		//Search all of the tables
		if ($_POST["searchTables"]){
			$searchTables = $_POST["searchTables"];
		}else{
			$searchTables = array("section", "faculty", "project", "partner");
		}
		
		keywordSearch($database, $_POST["keyword"], $searchTables);
	}
	
	if ($database)
	{
		$database->close();
	}

	?>
</body>
</html>
