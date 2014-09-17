<?php
/*
	Main Page
	Western Washington University
	Service Learning Center Database
	Created for CSCI-490 series by:
	Eddie Gebauer, John Miller, and Toan Nguyen
*/
include('database.php');
include_once('CAS.php');
phpCAS::client(CAS_VERSION_2_0, 'websso.wwu.edu', 443, '/cas');
phpCAS::setNoCasServerValidation();
if (isset($_REQUEST['logout'])) phpCAS::logout();
if (phpCAS::isAuthenticated()) $casuser = phpCAS::getUser();
?>

<!DOCTYPE HTML>
<html>
	<title>Service Learning Center Database</title>
	<head>
		<link href="bootstrap.css" rel="stylesheet">
		<link href="bootstrap/css/footer.css" rel="stylesheet">
	</head>
	<body>
		<img border="0" src="banner.jpg" width="100%" height="150">
		<ul class="nav nav-pills">
			<li class="active"><a href="main.php">Home</a></li>
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
				else if(phpCAS::isAuthenticated())
				{
					echo '<li><a>You are logged in as <font color="red">' . $casuser . '</font></li></a>';
					echo '<li><a href="?logout">(Logout)</li></a>';
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
	//Connect to the SLC database
	$database = connectSLC();
?>

<div class="container">
	Jump to:
	<a href="#project">Projects</a> |
	<a href="#section">Sections</a> |
	<a href="#partner">Partners</a> |
	<a href="#faculty">Faculty</a> |
	<a href="#course">Courses</a>

	<!------------------------------>
	<!--Projects-->
	<!------------------------------>
	
	<a name="project"></a>
	<table class="table table-striped">
		<thead>
			<h4 align="center"><u><i>Projects</i></u></h4>
			<tr>
				<th>Name</th>
				<th>Partner</th>
				<th>Students</th> 
				<th>Hours</th>
				<th>Type</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query = "	SELECT * FROM project
						LEFT JOIN partner ON project.projectFK_partnerID = partner.partnerID
						ORDER BY partnerName;";
			$result = mysqli_query($database, $query) or die(mysql_error());
			while ($row = mysqli_fetch_array($result))
			{
				echo '<tr>';
				echo '<td>' . $row['projectName'] . '</td>';
				echo '<td>' . $row['partnerName'] . '</td>';
				echo '<td>' . $row['projectNumStudents'] . '</td>';
				echo '<td>' . $row['projectHours'] . '</td>';
				echo '<td>' . $row['projectType'] . '</td>';
				echo '<td>' . $row['projectStatus'] . '</td>';
				echo '<form action=project.php method=post>';
				echo '<input type="hidden" name="projectID" value="'. $row['projectID'] .'">';
				echo '<td><input type="submit" method="post" value="Details' . '""></td></form>';
			}
		?>
		</tbody>
	</table>
	Jump to:
	<a href="#project">Projects</a> |
	<a href="#section">Sections</a> |
	<a href="#partner">Partners</a> |
	<a href="#faculty">Faculty</a> |
	<a href="#course">Courses</a>
	
	<!------------------------------>
	<!--Sections-->
	<!------------------------------>
	
	<hr style='background:#000000; border:0; height:3px' />
	<a name="section"></a>
	<table class="table table-striped">
		<thead>
			<h4 align="center"><u><i>Sections</i></u></h4>
			<tr>
				<th>CRN</th>
				<th>Course</th>
				<th>Name</th>
				<th>Teacher</th>
				<th>Quarter</th> 
				<th>Year</th>
				<th>Status</th>
				<th>Students</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query = "	SELECT * FROM section
						LEFT JOIN course ON section.sectionFK_courseID = course.courseID
						LEFT JOIN faculty ON section.sectionFK_facultyID = facultyID
						ORDER BY sectionYear DESC,
								CASE
									WHEN sectionQuarter = 'Summer' THEN 1
									WHEN sectionQuarter = 'Spring' THEN 2
									WHEN sectionQuarter = 'Winter' THEN 3
									WHEN sectionQuarter = 'Fall' THEN 4
									ELSE 5
								END;";
			$result = mysqli_query($database, $query) or die(mysql_error());
			while ($row = mysqli_fetch_array($result))
			{
				#Print Section Table
				echo '<tr>';
				echo '<td>' . $row['sectionCRN'] . '</td>';
				echo '<td>' . $row['courseDept'] . $row['courseNum'] . '</td>';
				echo '<td>' . $row['courseName'] . '</td>';
				echo '<td>' . $row['facultyName'] . '</td>';
				echo '<td>' . $row['sectionQuarter'] . '</td>';
				echo '<td>' . $row['sectionYear'] . '</td>';
				echo '<td>' . $row['sectionStatus'] . '</td>';
				echo '<td>' . $row['sectionNumStudents'] . '</td>';
				echo '<form action=section.php method=post>';
				echo '<input type="hidden" name="sectionID" value="'. $row['sectionID'] .'">';
				echo '<td><input type="submit" method="post" value="Details""></td></form>';
			}
		?>
		</tbody>
	</table>
	Jump to:
	<a href="#project">Projects</a> |
	<a href="#section">Sections</a> |
	<a href="#partner">Partners</a> |
	<a href="#faculty">Faculty</a> |
	<a href="#course">Courses</a>
	
	<!------------------------------>
	<!--Partners-->
	<!------------------------------>
	
	<hr style='background:#000000; border:0; height:3px' />
	<a name="partner"></a>
	<table class="table table-striped">
		<thead>
			<h4 align="center"><u><i>Partners</i></u></h4>
			<tr>
				<th>Name</th>
				<th>Contact</th> 
				<th>Type</th>
			</tr>
		</thead>
		<tbody>
			<?php
	$query = 'SELECT * FROM partner ORDER BY partnerName';
	$result = mysqli_query($database, $query) or die(mysql_error());
	while ($row = mysqli_fetch_array($result))
	{
		echo '<tr>';
		echo '<td>' . $row['partnerName'] . '</td>';
		echo '<td>' . $row['partnerContact'] . '</td>';
		echo '<td>' . $row['partnerType'] . '</td>';
		echo '<form action=partner.php method=post>';
		echo '<input type="hidden" name="partnerID" value="'. $row['partnerID'] .'">';
		echo '<td><input type="submit" method="post" value="Details""></td></form>';
	}

	?>
		</tbody>
	</table>
	Jump to:
	<a href="#project">Projects</a> |
	<a href="#section">Sections</a> |
	<a href="#partner">Partners</a> |
	<a href="#faculty">Faculty</a> |
	<a href="#course">Courses</a>
	
	<!------------------------------>
	<!--Faculty-->
	<!------------------------------>
	
	<hr style='background:#000000; border:0; height:3px' />
	<a name="faculty"></a>
	<table class="table table-striped">
		<thead>
			<h4 align="center"><u><i>Faculty</i></u></h4>
			<tr>
				<th>Name</th>
				<th>Department</th> 
				<th>Training?</th>
			</tr>
		</thead>
		<tbody>
			<?php
	$query = 'SELECT * FROM faculty ORDER BY facultyName';
	$result = mysqli_query($database, $query) or die(mysql_error());
	while ($row = mysqli_fetch_array($result))
	{
		
		if ($row['facultyTraining'] == "1") $training = 'Yes';
		else if ($row['facultyTraining'] == "0") $training = 'No';
		else $training = '';
		echo '<tr>';
		echo '<td>' . $row['facultyName'] . '</td>';
		echo '<td>' . $row['facultyDept'] . '</td>';
		echo '<td>' . $training . '</td>';
		echo '<form action=faculty.php method=post>';
		echo '<input type="hidden" name="facultyID" value="'. $row['facultyID'] .'">';
		echo '<td><input type="submit" method="post" value="Details""></td></form>';
	}

	?>
		</tbody>
	</table>
	Jump to:
	<a href="#project">Projects</a> |
	<a href="#section">Sections</a> |
	<a href="#partner">Partners</a> |
	<a href="#faculty">Faculty</a> |
	<a href="#course">Courses</a>
	
	<!------------------------------>
	<!--Courses-->
	<!------------------------------>
	
	<hr style='background:#000000; border:0; height:3px' />
	<a name="course"></a>
	<table class="table table-striped">
		<thead>
			<h4 align="center"><u><i>Courses</i></u></h4>
			<tr>
				<th>Course</th>
				<th>Name</th> 
				<th>Details</th>
			</tr>
		</thead>
		<tbody>
			<?php
	$query = 'SELECT * FROM course ORDER BY courseDept, courseNum';
	$result = mysqli_query($database, $query) or die(mysql_error());
	while ($row = mysqli_fetch_array($result))
	{
		echo '<tr>';
		echo '<td>' . $row['courseDept'] . $row['courseNum'] . '</td>';
		echo '<td>' . $row['courseName'] . '</td>';
		echo '<form action="course.php" method="post">';
		echo '<input type="hidden" name="courseID" value="'. $row['courseID'] .'">';
		echo '<td><input type="submit" method="post" value="Details"></td></form>';
	}

	?>
		</tbody>
	</table>
	Jump to:
	<a href="#project">Projects</a> |
	<a href="#section">Sections</a> |
	<a href="#partner">Partners</a> |
	<a href="#faculty">Faculty</a> |
	<a href="#course">Courses</a>
</div>
</body>
</html>
