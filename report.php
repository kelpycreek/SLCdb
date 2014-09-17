<?php
/*
	Report
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
	<body>
		<title>
				Report
		</title>
		<link href="bootstrap.css" rel="stylesheet">
			<img border="0" src="banner.jpg" width="100%" height="150">
		<ul class="nav nav-pills">
			<li><a href="main.php">Home</a></li>
			<li><a href="info.php">Info</a></li>
			<li><a href="add.php">Add</a></li>
			<li class="active"><a href="report.php">Report</a></li>
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
		</body>
		
		<?php
		
		//Connect to the SLC database
		$database = connectSLC();
		
		//--------------------
		//
		//REPORT FORMS
		//
		//--------------------
		if (!isset($_POST["report"]))
		{ ?>
			<h3><u><b>Sections</b></u></h3>
			
			<h4>By Quarter</h4>
			Show all sections from 
			<form action="report.php" method="POST">
				<select required name="quarter">
					<option disabled>Select a quarter...</option>
					<option value="Fall">Fall</option>
					<option value="Winter">Winter</option>
					<option value="Spring">Spring</option>
					<option value="Summer">Summer</option>
				</select>
				<input required type="text" name="year" placeholder="Year"></input>
				<br>
				<input type="hidden" name="type" value="section-quarter">
				<input type="submit" name="report" value="Report">
			</form>
			
			<h4>By Status</h4>
			Show all sections that are currently
			<form action="report.php" method="POST">
				<select required name="sstat">
					<option disabled selected>Select a section status...</option>
					<?php
					$query = "SELECT DISTINCT sectionStatus FROM section;";
					$result = $database->fullQuery($query);
					while ($row = mysqli_fetch_array($result))
					{
						if ($row["sectionStatus"] != "")
							echo "<option value='{$row["sectionStatus"]}'>{$row["sectionStatus"]}</option>";
					}
					?>
				</select>
				<br>
				<input type="hidden" name="type" value="section-status">
				<input type="submit" name="report" value="Report">
			</form>
			
			
			<h4>By Affiliation</h4>
			Show all sections without an affiliated project
			<form action="report.php" method="POST">
				<input type="hidden" name="type" value="section-aff">
				<input type="submit" name="report" value="Report">
			</form>
			
			<hr style='background:#000000; border:0; height:3px' />
			
			<h3><u><b>Projects</b></u></h3>
			
			<h4>By Type</h4>
			Show all projects of the type
			<form action="report.php" method="POST">
				<select required name="ptype">
					<option disabled selected>Select a project type...</option>
					<?php
					$query = "SELECT DISTINCT projectType FROM project;";
					$result = $database->fullQuery($query);
					while ($row = mysqli_fetch_array($result))
					{
						if ($row["projectType"] != "")
							echo "<option value='{$row["projectType"]}'>{$row["projectType"]}</option>";
					}
					?>
				</select>
				<br>
				<input type="hidden" name="type" value="project-type">
				<input type="submit" name="report" value="Report">
			</form>
			
			<h4>By Status</h4>
			Show all projects that are currently
			<form action="report.php" method="POST">
				<select required name="pstat">
					<option disabled selected>Select a project status...</option>
					<?php
					$query = "SELECT DISTINCT projectStatus FROM project;";
					$result = $database->fullQuery($query);
					while ($row = mysqli_fetch_array($result))
					{
						if ($row["projectStatus"] != "")
							echo "<option value='{$row["projectStatus"]}'>{$row["projectStatus"]}</option>";
					}
					?>
				</select>
				<br>
				<input type="hidden" name="type" value="project-status">
				<input type="submit" name="report" value="Report">
			</form>
			
			<h4>By Affiliation</h4>
			Show all projects without an affiliated section
			<form action="report.php" method="POST">
				<input type="hidden" name="type" value="project-aff">
				<input type="submit" name="report" value="Report">
			</form>
		<? }
		
		//--------------------
		//
		//DISPLAY REPORT
		//
		//--------------------
		else
		{
			//--------------------
			//SECTIONS BY QUARTER
			//--------------------
			if ($_POST["type"] == "section-quarter")
			{
				echo "Sections from " . $_POST["quarter"] . ' ' . $_POST["year"] . ":<br>";
				
				//Query
				$quarter = $_POST["quarter"];
				$year = $_POST["year"];
				if ($quarter != "")
					$query =	"SELECT * FROM section
								LEFT JOIN course ON section.sectionFK_courseID = course.courseID
								LEFT JOIN faculty ON section.sectionFK_facultyID = faculty.facultyID
								WHERE sectionQuarter = '{$quarter}'
								AND sectionYear = {$year}
								ORDER BY sectionYear DESC;";
				else
					$query =	"SELECT * FROM section
								LEFT JOIN course ON section.sectionFK_courseID = course.courseID
								LEFT JOIN faculty ON section.sectionFK_facultyID = faculty.facultyID
								WHERE sectionYear = {$year}
								ORDER BY sectionYear DESC,
								CASE
									WHEN sectionQuarter = 'Summer' THEN 1
									WHEN sectionQuarter = 'Spring' THEN 2
									WHEN sectionQuarter = 'Winter' THEN 3
									WHEN sectionQuarter = 'Fall' THEN 4
									ELSE 5
								END;";
				$result = $database->fullQuery($query);
				
				//Table header
				echo '	<table class="table table-striped" style="width:30%">
								<thead>
									<th>Course</th>
									<th>Name</th>
									<th>Instructor</th>
									<th>Quarter</th>
									<th>Year</th>
									<th>Status</th>
									<th>CRN</th>
									<th>Students</th>
								</thead>';
								
				//Table body
				echo '<tbody>';
				while ($row = mysqli_fetch_array($result))
				{
					echo '<tr>';
						echo '<td>' . $row["courseDept"] . $row["courseNum"] . '</td>';
						echo '<td>' . $row["courseName"] . '</td>';
						echo '<td>' . $row["facultyName"] . '</td>';
						echo '<td>' . $row["sectionQuarter"] . '</td>';
						echo '<td>' . $row["sectionYear"] . '</td>';
						echo '<td>' . $row["sectionStatus"] . '</td>';
						echo '<td>' . $row["sectionCRN"] . '</td>';
						echo '<td>' . $row["sectionNumStudents"] . '</td>';
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
				
				//Export button
				echo '	<form action="export.php" method="post">
							<button type="submit" name="export" value="' . $query . '">Export</button>
							<input type="hidden" name="outname" value="report">
						</form>';
			}
			//--------------------
			//SECTIONS BY STATUS
			//--------------------
			if ($_POST["type"] == "section-status")
			{
				$stat = $_POST["sstat"];
				echo "Sections of status '" . $stat . "':<br>";
				
				//Query
				$query =	"SELECT * FROM section
							LEFT JOIN course ON section.sectionFK_courseID = course.courseID
							LEFT JOIN faculty ON section.sectionFK_facultyID = faculty.facultyID
							WHERE sectionStatus = '{$stat}'
							ORDER BY sectionYear DESC,
								CASE
									WHEN sectionQuarter = 'Summer' THEN 1
									WHEN sectionQuarter = 'Spring' THEN 2
									WHEN sectionQuarter = 'Winter' THEN 3
									WHEN sectionQuarter = 'Fall' THEN 4
									ELSE 5
								END;;";
				$result = $database->fullQuery($query);
				
				//Table header
				echo '	<table class="table table-striped" style="width:30%">
								<thead>
									<th>Course</th>
									<th>Name</th>
									<th>Instructor</th>
									<th>Quarter</th>
									<th>Year</th>
									<th>Status</th>
									<th>CRN</th>
									<th>Students</th>
								</thead>';
								
				//Table body
				echo '<tbody>';
				while ($row = mysqli_fetch_array($result))
				{
					echo '<tr>';
						echo '<td>' . $row["courseDept"] . $row["courseNum"] . '</td>';
						echo '<td>' . $row["courseName"] . '</td>';
						echo '<td>' . $row["facultyName"] . '</td>';
						echo '<td>' . $row["sectionQuarter"] . '</td>';
						echo '<td>' . $row["sectionYear"] . '</td>';
						echo '<td>' . $row["sectionStatus"] . '</td>';
						echo '<td>' . $row["sectionCRN"] . '</td>';
						echo '<td>' . $row["sectionNumStudents"] . '</td>';
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
				
				//Export button
				echo '	<form action="export.php" method="post">
							<button type="submit" name="export" value="' . $query . '">Export</button>
							<input type="hidden" name="outname" value="report">
						</form>';
			}
			
			//--------------------
			//SECTIONS BY AFFILIATION
			//--------------------
			if ($_POST["type"] == "section-aff")
			{
				echo "Sections without an affiliated project:<br>";
				
				//Query
				$query =	"SELECT * FROM section
							LEFT JOIN course ON section.sectionFK_courseID = course.courseID
							LEFT JOIN faculty ON section.sectionFK_facultyID = faculty.facultyID;";
				$sections = $database->fullQuery($query);
				$query =	"SELECT * FROM affiliate;";
				$affiliates = $database->fullQuery($query);
				
				$toprint = array();
				while ($sec = mysqli_fetch_array($sections))
				{
					$ok = true;
					while ($aff = mysqli_fetch_array($affiliates))
					{
						if ($aff["aff_sectionID"] == $sec["sectionID"])
						{
							$ok = false;
						}
					}
					if ($ok == true)
					{
						$toprint[count($toprint)] = $sec;
					}
					$affiliates->data_seek(0);
				}
				
				//Table header
				echo '	<table class="table table-striped" style="width:30%">
								<thead>
									<th>Course</th>
									<th>Name</th>
									<th>Instructor</th>
									<th>Quarter</th>
									<th>Year</th>
									<th>Status</th>
									<th>CRN</th>
									<th>Students</th>
									<th>Details</th>
								</thead>';
								
				//Table body
				echo '<tbody>';
				for ($i = 0; $i < count($toprint); $i++)
				{
					$row = $toprint[$i];
					echo '<tr>';
						echo '<td>' . $row["courseDept"] . $row["courseNum"] . '</td>';
						echo '<td>' . $row["courseName"] . '</td>';
						echo '<td>' . $row["facultyName"] . '</td>';
						echo '<td>' . $row["sectionQuarter"] . '</td>';
						echo '<td>' . $row["sectionYear"] . '</td>';
						echo '<td>' . $row["sectionStatus"] . '</td>';
						echo '<td>' . $row["sectionCRN"] . '</td>';
						echo '<td>' . $row["sectionNumStudents"] . '</td>';
						echo '<td>	<form action="section.php" method="POST">
									<input type="hidden" name="sectionID" value="' . $row["sectionID"] . '">
									<input type="submit" value="Details"></form></td>';
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
			}
			
			//--------------------
			//PROJECTS BY TYPE
			//--------------------
			if ($_POST["type"] == "project-type")
			{
			echo "Projects of type '" . $_POST["ptype"] . "'<br>";
				
				//Query
				$type = $_POST["ptype"];
				$query =	"SELECT * FROM project
							LEFT JOIN partner ON project.projectFK_partnerID = partner.partnerID
							WHERE projectType = '{$type}';";
				$result = $database->fullQuery($query);
				
				//Table header
				echo '	<table class="table table-striped" style="width:30%">
								<thead>
									<th>Name</th>
									<th>Partner</th>
									<th>Type</th>
									<th>Status</th>
									<th>Hours</th>
									<th>Students</th>
									<th>Time Sensitive?</th>
								</thead>';
								
				//Table body
				echo '<tbody>';
				while ($row = mysqli_fetch_array($result))
				{
					echo '<tr>';
						echo '<td>' . $row["projectName"] . '</td>';
						echo '<td>' . $row["partnerName"] . '</td>';
						echo '<td>' . $row["projectType"] . '</td>';
						echo '<td>' . $row["projectStatus"] . '</td>';
						echo '<td>' . $row["projectHours"] . '</td>';
						echo '<td>' . $row["projectNumStudents"] . '</td>';
						echo '<td>' . $row["projectTimeSensitive"] . '</td>';
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
				
				//Export button
				echo '	<form action="export.php" method="post">
							<button type="submit" name="export" value="' . $query . '">Export</button>
							<input type="hidden" name="outname" value="report">
						</form>';
			}
			
			//--------------------
			//PROJECTS BY STATUS
			//--------------------
			if ($_POST["type"] == "project-status")
			{
				echo "Projects of status '" . $_POST["pstat"] . "'<br>";
				
				//Query
				$stat = $_POST["pstat"];
				$query =	"SELECT * FROM project
							LEFT JOIN partner ON project.projectFK_partnerID = partner.partnerID
							WHERE projectStatus = '{$stat}';";
				$result = $database->fullQuery($query);
				
				//Table header
				echo '	<table class="table table-striped" style="width:30%">
								<thead>
									<th>Name</th>
									<th>Partner</th>
									<th>Type</th>
									<th>Status</th>
									<th>Hours</th>
									<th>Students</th>
									<th>Time Sensitive?</th>
								</thead>';
								
				//Table body
				echo '<tbody>';
				while ($row = mysqli_fetch_array($result))
				{
					echo '<tr>';
						echo '<td>' . $row["projectName"] . '</td>';
						echo '<td>' . $row["partnerName"] . '</td>';
						echo '<td>' . $row["projectType"] . '</td>';
						echo '<td>' . $row["projectStatus"] . '</td>';
						echo '<td>' . $row["projectHours"] . '</td>';
						echo '<td>' . $row["projectNumStudents"] . '</td>';
						echo '<td>' . $row["projectTimeSensitive"] . '</td>';
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
				
				//Export button
				echo '	<form action="export.php" method="post">
							<button type="submit" name="export" value="' . $query . '">Export</button>
							<input type="hidden" name="outname" value="report">
						</form>';
			}
			
			//--------------------
			//PROJECTS BY AFFILIATION
			//--------------------
			if ($_POST["type"] == "project-aff")
			{
				echo "Projects without an affiliated section:<br>";
				
				//Query
				$query =	"SELECT * FROM project
							LEFT JOIN partner ON project.projectFK_partnerID = partner.partnerID;";
				$projects = $database->fullQuery($query);
				$query =	"SELECT * FROM affiliate;";
				$affiliates = $database->fullQuery($query);
				
				$toprint = array();
				while ($prj = mysqli_fetch_array($projects))
				{
					$ok = true;
					while ($aff = mysqli_fetch_array($affiliates))
					{
						if ($aff["aff_projectID"] == $prj["projectID"])
						{
							$ok = false;
						}
					}
					if ($ok == true)
					{
						$toprint[count($toprint)] = $prj;
					}
					$affiliates->data_seek(0);
				}
				
				//Table header
				echo '	<table class="table table-striped" style="width:30%">
								<thead>
									<th>Name</th>
									<th>Partner</th>
									<th>Type</th>
									<th>Status</th>
									<th>Hours</th>
									<th>Students</th>
									<th>Time Sensitive?</th>
									<th>Details</th>
								</thead>';
								
				//Table body
				echo '<tbody>';
				for ($i = 0; $i < count($toprint); $i++)
				{
					$row = $toprint[$i];
					if ($row["projectTimeSensitive"] == "1")
						$ts = "Yes";
					else if ($row["projectTimeSensitive"] == "0")
						$ts = "No";
					else
						$ts = "";
					echo '<tr>';
						echo '<td>' . $row["projectName"] . '</td>';
						echo '<td>' . $row["partnerName"] . '</td>';
						echo '<td>' . $row["projectType"] . '</td>';
						echo '<td>' . $row["projectStatus"] . '</td>';
						echo '<td>' . $row["projectHours"] . '</td>';
						echo '<td>' . $row["projectNumStudents"] . '</td>';
						echo '<td>' . $ts . '</td>';
						echo '<td>	<form action="project.php" method="POST">
									<input type="hidden" name="projectID" value="' . $row["projectID"] . '">
									<input type="submit" value="Details"></form></td>';
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
			}
		}
		?>
	</body>
</html>
