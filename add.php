<?php
/*
	Add
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
		<link href="bootstrap.css" rel="stylesheet">
	</head>
	<body>
		<title>
			Add
		</title>
		
		<!-- Navigation bar -->
		<img border="0" src="banner.jpg" width="100%" height="150">
		<ul class="nav nav-pills">
			<li><a href="main.php">Home</a></li>
			<li><a href="info.php">Info</a></li>
			<li class="active"><a href="add.php">Add</a></li>
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
					echo '<li><a>You are logged in as <font color="red">' . $casuser . '</font></li></a>';
					echo '<li><a href="?logout">(Logout)</li></a>';
				}
			?>
			<li>
				<form action="keyword.php" method=POST>
					<input type=text align="center" style="width:25em" name="keyword" placeholder="Search...">
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
		//--------------------
		//SUBMIT CHANGES TO DATABASE
		//--------------------
		
		//Connect to the SLC database
		$database = connectSLC();

		//Get the date and time
		$t = time();
		$format = "n/j/y g:ia";
		$dateFormat = new DateTime(date($format, $t));
		$date = $dateFormat->format($format);
		
		//Submit this string to POST to determine which entity to add
		$submit = $_POST["submit"];
		
		//Section
		if ($submit == "Add Section")
		{
			$sectionQuarter = $_POST["sectionQuarter"];
			if ($sectionQuarter != "NULL")
				$sectionQuarter = "'" . $sectionQuarter . "'";
			$sectionStatus = $_POST["sectionStatusText"] ? $_POST["sectionStatusText"] : $_POST["sectionStatus"];
			if ($sectionStatus != "NULL")
				$sectionStatus = "'" . $sectionStatus . "'";
			$sectionNumStudents = $_POST["sectionNumStudents"] ? $_POST["sectionNumStudents"] : '0';
			$courseID = $_POST["courseID"] ? $_POST["courseID"] : "NULL";
			$facultyID = $_POST["facultyID"] ? $_POST["facultyID"] : "NULL";
			
			$query = "	INSERT INTO section
									(sectionCRN, sectionQuarter, sectionYear, sectionNumStudents, sectionStatus,
									sectionSL, sectionRefl, sectionEval, sectionPres, sectionDesc,
									sectionCreator, sectionLastMod, sectionFK_courseID, sectionFK_facultyID)
						VALUES		('{$_POST["sectionCRN"]}', {$sectionQuarter}, '{$_POST["sectionYear"]}', {$sectionNumStudents}, {$sectionStatus},
									{$_POST["sectionSL"]}, {$_POST["sectionRefl"]}, {$_POST["sectionEval"]}, {$_POST["sectionPres"]}, {$_POST["sectionDesc"]},
									'{$casuser}', '{$date}', {$courseID}, {$facultyID}); 
						SELECT LAST_INSERT_ID();";
						
			$return1 = $database->fullQuery($query);
			$result1 = $database->store_result();	//Result from INSERT query (should be FALSE)
			$return2 = $database->next_result();	//Skip to next result
			$result2 = $database->store_result();	//Result from LAST_INSERT_ID()
			$row = mysqli_fetch_array($result2);
			$newID = $row[0];
			
			echo ("Successfully added new section!<br>");
			if ($sectionQuarter == "NULL")
				$sectionQuarter == "";
			echo ("CRN " . $_POST["sectionCRN"] . " (" . $sectionQuarter . " " . $_POST["sectionYear"] . ")<br>");
			
			//Insert extra section comments
			$reflComment = $_POST["reflComment"];
			$evalComment = $_POST["evalComment"];
			$presComment = $_POST["presComment"];
			$descComment = $_POST["descComment"];
			
			//Reflections
			if ($reflComment != "")
			{
				$reflComment = "Reflections: " . $reflComment;
				$return = $database->fullQuery("INSERT INTO sectionComments
												(sectionCom, sectionComCreator, sectionComLastMod, comFK_sectionID)
												VALUES ('{$reflComment}', '{$casuser}', '{$date}', {$newID});");
				echo ($reflComment . "<br>");
			}

			//Evaluations
			if ($evalComment != "")
			{
				$evalComment = "Evaluations: " . $evalComment;
				$return = $database->fullQuery("INSERT INTO sectionComments
												(sectionCom, sectionComCreator, sectionComLastMod, comFK_sectionID)
												VALUES ('{$evalComment}', '{$casuser}', '{$date}', {$newID});");
				echo ($evalComment . "<br>");
			}

			//Presentations
			if ($presComment != "")
			{
				$presComment = "Presentations: " . $presComment;
				$return = $database->fullQuery("INSERT INTO sectionComments
												(sectionCom, sectionComCreator, sectionComLastMod, comFK_sectionID)
												VALUES ('{$presComment}', '{$casuser}', '{$date}', {$newID});");
				echo ($presComment . "<br>");
			}

			//Description Sheet
			if ($descComment != "")
			{
				$descComment = "Description Sheet: " . $descComment;
				$return = $database->fullQuery("INSERT INTO sectionComments
												(sectionCom, sectionComCreator, sectionComLastMod, comFK_sectionID)
												VALUES ('{$descComment}', '{$casuser}', '{$date}', {$newID});");
				echo ($descComment . "<br>");
			}
		}

		//Faculty
		else if($submit == "Add Faculty")
		{
			$facultyDept = $_POST["facultyDeptText"] ? $_POST["facultyDeptText"] : $_POST["facultyDept"];
			if ($facultyDept != "NULL")
				$facultyDept = "'" . $facultyDept . "'";
				
			$query = "	INSERT INTO faculty
								(facultyName, facultyDept, facultyTraining,
								facultyDesc, facultyCreator, facultyLastMod)
						VALUES	('{$_POST["facultyName"]}', {$facultyDept}, {$_POST["facultyTraining"]},
								'{$_POST["facultyDesc"]}', '{$casuser}', '{$date}');";
								
			$return = $database->fullQuery($query);
			echo ("Successfully added new Faculty:<br>");
			echo ($_POST["facultyName"] . " (" . $facultyDept . ")<br>");
			echo $_POST["facultyDesc"] . "<br>";
		}

		//Partner
		else if($submit == "Add Partner")
		{
			$partnerType = $_POST["partnerTypeText"] ? $_POST["partnerTypeText"] : $_POST["partnerType"];
			if ($partnerType != "NULL")
				$partnerType = "'" . $partnerType . "'";
				
			$query = "	INSERT INTO partner
								(partnerName, partnerContact, partnerType, partnerDesc, partnerCreator, partnerLastMod)
						VALUES	('{$_POST["partnerName"]}', '{$_POST["partnerContact"]}', {$partnerType},
								'{$_POST["partnerDesc"]}', '{$casuser}', '{$date}');";
								
			$return = $database->fullQuery($query);
			echo ("Successfully added new partner:<br>");
			echo ($_POST["partnerName"] . "<br>");
			echo ($_POST["partnerDesc"] . "<br>");
		}

		//Project
		else
		if ($submit == "Add Project")
		{
			$projectType = $_POST["projectTypeText"] ? $_POST["projectTypeText"] : $_POST["projectType"];
			$projectStatus = $_POST["projectStatusText"] ? $_POST["projectStatusText"] : $_POST["projectStatus"];
			$projectNumStudents = $_POST["projectNumStudents"] ? $_POST["projectNumStudents"] : '0';
			$projectHours = $_POST["projectHours"] ? $_POST["projectHours"]: '0';
			$partnerID = $_POST["partnerID"] ? $_POST["partnerID"] : "NULL";
			if ($projectType != "NULL")
				$projectType = "'" . $projectType . "'";
			if ($projectStatus != "NULL")
				$projectStatus = "'" . $projectStatus . "'";
				
			$query = "	INSERT INTO project
								(projectName, projectNumStudents, projectHours, projectType, projectStatus,
								projectTimeSensitive, projectCreator, projectLastMod, projectFK_partnerID)
						VALUES	('{$_POST["projectName"]}', {$projectNumStudents}, {$projectHours}, {$projectType},
								{$projectStatus}, {$_POST["projectTimeSensitive"]}, '{$casuser}', '{$date}', {$partnerID});";
						
			$return = $database->fullQuery($query);
			echo ("Successfully added new project:<br>");
			echo ($_POST["projectName"] . " (" . $projectStatus . ")<br>");
			echo ($_POST["projectDesc"] . "<br>");
		}

		//Course
		else
		if($submit == "Add Course")
		{
			$courseDept = $_POST["courseDeptText"] ? $_POST["courseDeptText"] : $_POST["courseDept"];
			if ($courseDept != "NULL")
				$courseDept = "'" . $courseDept . "'";
			$query = "	INSERT INTO course
								(courseNum, courseDept, courseName, courseDesc, courseCreator, courseLastMod)
						VALUES	('{$_POST["courseNum"]}', {$courseDept}, '{$_POST["courseName"]}', '{$_POST["courseDesc"]}', '{$casuser}', '{$date}');";
						
			$return = $database->fullQuery($query);
			echo ("Successfully added new course:<br>");
			if ($courseDept = "NULL")
				$courseDept = "";
			echo ($courseDept . $_POST["courseNum"] . ": " . $_POST["courseName"] . "<br>");
			echo ($_POST["courseDesc"] . "<br>");
		}
		//--------------------
		//END SUBMIT CHANGES
		//--------------------
	?>
	
		<!-- FORMS -->
	
		<hr style='background:#000000; border:0; height:3px' />
		
		<!--PARTNER FORM-->
		<h4 align="center"><u><i>Add Partner</i></u></h4>
		<form action=add.php method=post id="partnerForm">
			<table class="table table-striped" style="width:30%">
				<thead>
					<tr>
						<th>Name</th>
						<th>Type</th>
						<th>Contact Info</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><input type="text" name="partnerName" placeholder="Name"></td>
						<td>
							<select name="partnerType">
								<option value="NULL">[None]</option>
								<?php
									$result = $database->fullQuery("SELECT DISTINCT partnerType FROM partner ORDER BY partnerType;");
									while ($row = mysqli_fetch_array($result))
									{
										$type = $row["partnerType"];
										if ($type != "")
										{
											echo "<option value=\"{$type}\">{$type}</option>";
										}
									}
								?>
							</select>
							<input type="text" name="partnerTypeText" placeholder="Add New">
						</td>
						<td><input type="text" name="partnerContact" placeholder="Contact"></td>
					</tr>
				</tbody>
			</table>
			<textarea rows="3" cols="100" name="partnerDesc" style="width:800px" form="partnerForm" placeholder="Description"></textarea>
			<br>
			<input type="submit" name="submit" value="Add Partner">
		</form>
		
		<hr style="background:#000000; border:0; height:3px" />
		
		<!--FACULTY FORM-->
		<h4 align="center"><u><i>Add Faculty</i></u></h4>
		<form action=add.php method=post id="facultyForm">
			<table class="table table-striped" style="width:30%">
				<thead>
					<tr>
						<th>Faculty Name</th>
						<th>Department</th>
						<th>Trained?</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><input type="text" name="facultyName" placeholder="Name"></td>
						<td>
							<select name="facultyDept">
								<option value="NULL">[None]</option>
								<?php
									$result = $database->fullQuery("SELECT DISTINCT facultyDept AS dept FROM faculty
																	UNION
																	SELECT DISTINCT courseDept AS dept FROM course
																	ORDER BY dept");
									while ($row = mysqli_fetch_array($result))
									{
										$dept = $row["dept"];
										if ($dept != "")
										{
											echo "<option value=\"{$dept}\">{$dept}</option>";
										}
									}
								?>
							</select>
							<input type="text" name="facultyDeptText" placeholder="Add New">
						</td>
						<td>
							<select name="facultyTraining">
								<option value="NULL">[Unknown]</option>
								<option value="0">No</option>
								<option value="1">Yes</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<!--DESCRIPTION-->
			<textarea rows="3" cols="100" name="facultyDesc" style="width:800px" form="facultyForm" placeholder="Description"></textarea>
			<br>
			<input type="submit" name="submit" value="Add Faculty">
		</form>
		
		<hr style="background:#000000; border:0; height:3px" />
		
		<!--COURSE FORM-->
		<h4 align="center"><u><i>Add Course</i></u></h4>
		<form action=add.php method=post id="courseForm">
			<table class="table table-striped" style="width:30%">
				<thead>
					<tr>
						<th>Department</th>
						<th>Course Number</th>
						<th>Course Name</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<select name="courseDept">
								<option value="NULL">[None]</option>
								<?php
									$result = $database->fullQuery("SELECT DISTINCT facultyDept AS dept FROM faculty
																	UNION
																	SELECT DISTINCT courseDept AS dept FROM course
																	ORDER BY dept");
									while ($row = mysqli_fetch_array($result))
									{
										$dept = $row["dept"];
										if ($dept != "")
										{
											echo "<option value=\"{$dept}\">{$dept}</option>";
										}
									}
								?>
							</select>
							<input type="text" name="courseDeptText" placeholder="Add New">
						</td>
						<td><input type="text" name="courseNum" size="10" placeholder="Number"></td>
						<td><input type="text" name="courseName" size="20" placeholder="Name"></td>
					</tr>
				</tbody>
			</table>
			<textarea rows="3" cols="100" name="courseDesc" style="width:800px" form="courseForm" placeholder="Description"></textarea>
			<br>
			<input type="submit" name="submit" value="Add Course">
		</form>
		
		<hr style="background:#000000; border:0; height:3px" />
		
		<!--SECTION FORM-->
		<h4 align="center"><u><i>Add Section</i></u></h4>
		<form action=add.php method=post id="sectionForm">
			<!--TOP ROW-->
			<table class="table" style="width:30%">
				<thead>
					<tr>
						<th>Course</th>
						<th>Instructor</th>
						<th>Quarter</th>
						<th>Year</th>
						<th>Status</th>
						<th>Students</th>
						<th>CRN</th>
						<th>SL Attribute?</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<select name="courseID">
								<option value="NULL">[None]</option>
								<?php
									$result = $database->fullQuery("SELECT * FROM course
																	ORDER BY courseDept, courseNum");
									while ($row = mysqli_fetch_array($result))
									{
										$dept = $row["courseDept"];
										$num = $row["courseNum"];
										$name = $row["courseName"];
										$cid = $row["courseID"];
										if ($dept != "")
										{
											echo "<option value=\"{$cid}\">{$dept}{$num} - {$name}</option>";
										}
									}
								?>
							</select>
						</td>
						<td>
							<select name="facultyID">
								<option value="NULL">[None]</option>
								<?php
									$result = $database->fullQuery("SELECT * FROM faculty
																	ORDER BY facultyName");
									while ($row = mysqli_fetch_array($result))
									{
										$dept = $row["facultyDept"];
										$name = $row["facultyName"];
										$fid = $row["facultyID"];
										if ($dept != "")
										{
											echo "<option value=\"{$fid}\">{$name} ({$dept})</option>";
										}
									}
								?>
							</select>
						</td>
						<td>
							<select name="sectionQuarter" style="width:105px">
								<option value="NULL">[Unknown]</option>
								<option value="Spring">Spring</option>
								<option value="Summer">Summer</option>
								<option value="Fall">Fall</option>
								<option value="Winter">Winter</option>
							</select>
						</td>
						<td><input type="text" style="width:50px" name="sectionYear" placeholder="Year"></td>
						<td>
							<select name="sectionStatus">
								<option value="NULL">[Unknown]</option>
								<?php
									$result = $database->fullQuery("SELECT DISTINCT sectionStatus
																	FROM section
																	ORDER BY sectionStatus");
									while ($row = mysqli_fetch_array($result))
									{
										$stat = $row["sectionStatus"];
										if ($stat != "")
										{
											echo "<option value=\"{$stat}\">{$stat}</option>";
										}
									}
								?>
							</select>
							<input type="text" name="sectionStatusText" placeholder="Add New">
						</td>
						<td><input type="text" style="width:50px" name="sectionNumStudents" placeholder="Size"></td>
						<td><input type="text" style="width:100px" name="sectionCRN" placeholder="CRN"></td>
						<td>
							<select style="width:105px" name=sectionSL>
								<option value="NULL">[Unknown]</option>
								<option value="0">No</option>
								<option value="1">Yes</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<!--REFLECTIONS-->
			<b>Reflections required?</b>
			<br>
			<select style="width:105px" name=sectionRefl>
				<option value="NULL">[Unknown]</option>
				<option value="0">No</option>
				<option value="1">Yes</option>
			</select>
			<input type="text" style="width:1000px" name="reflComment" placeholder="Comments">
			<br>
			<!--EVALUATIONS-->
			<b>Evaluations required?</b>
			<br>
			<select style="width:105px" name=sectionEval>
				<option value="NULL">[Unknown]</option>
				<option value="0">No</option>
				<option value="1">Yes</option>
			</select>
			<input type="text" style="width:1000px" name="evalComment" placeholder="Comments">
			<br>
			<!--PRESENTATIONS-->
			<b>Presentations required?</b>
			<br>
			<select style="width:105px" name=sectionPres>
				<option value="NULL">[Unknown]</option>
				<option value="0">No</option>
				<option value="1">Yes</option>
			</select>
			<input type="text" style="width:1000px" name="presComment" placeholder="Comments">
			<br>
			<!--DESCRIPTION-->
			<b>Description sheet required?</b>
			<br>
			<select style="width:105px" name=sectionDesc>
				<option value="NULL">[Unknown]</option>
				<option value="0">No</option>
				<option value="1">Yes</option>
			</select>
			<input type="text" style="width:1000px" name="descComment" placeholder="Comments">
			<br>
			<!--SUBMIT-->
			<input type="hidden" name="profile" value="Section">
			<input type="hidden" name="sectionID">
			<input type="submit" name="submit" value="Add Section">
		</form>
		
		<hr style="background:#000000; border:0; height:3px" />
		
		<!--PROJECT FORM-->
		<h4 align="center"><u><i>Add Project</i></u></h4>
		<form action=add.php method=post id="projectForm">
			<!--TOP ROW-->
			<table class="table table-striped" style="width:30%">
				<thead>
					<tr>
						<th>Name</th>
						<th>Type</th>
						<th>Status</th>
						<th>Time Sensitive?</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><input type="text" name="projectName" placeholder="Name"></td>
						<td>
							<select name="projectType">
								<option value="NULL">[None]</option>
								<?php
									$result = $database->fullQuery("SELECT DISTINCT projectType FROM project
																	ORDER BY projectType");
									while ($row = mysqli_fetch_array($result))
									{
										$type = $row["projectType"];
										if ($type != "")
										{
											echo "<option value=\"{$type}\">{$type}</option>";
										}
									}
								?>
							</select>
							<input type="text" name="projectTypeText" placeholder="Add New">
						</td>
						<td>
							<select name="projectStatus">
								<option value="NULL">[None]</option>
								<?php
									$result = $database->fullQuery("SELECT  DISTINCT projectStatus FROM project
																	ORDER BY projectStatus");
									while ($row = mysqli_fetch_array($result))
									{
										$stat = $row["projectStatus"];
										if ($stat != "")
										{
											echo "<option value=\"{$stat}\">{$stat}</option>";
										}
									}
								?>
							</select>
							<input type="text" name="projectStatusText" placeholder="Add New">
						</td>
						<td>
						    <select name="projectTimeSensitive">
						    	<option value="NULL">[Unknown]</option>
						        <option value="0">No</option>
						        <option value="1">Yes</option>
						    </select>
						</td>
					</tr>
				</tbody>
			</table> 
			<!--BOTTOM ROW-->
			<table class="table table-striped" style="width:30%">
				<thead>
					<tr>
						<th>Partner</th>
						<th>Students</th>
						<th>Hours</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<select name="partnerID">
								<option value="NULL">[None]</option>
								<?php
									$result = $database->fullQuery("SELECT partnerID, partnerName FROM partner
																	ORDER BY partnerName");
									while ($row = mysqli_fetch_array($result))
									{
										$name = $row["partnerName"];
										$pid = $row["partnerID"];
										if ($dept != "")
										{
											echo "<option value=\"{$pid}\">{$name}</option>";
										}
									}
								?>
							</select>
						</td>
						<td><input type="text" name="projectNumStudents" style="width:50px" placeholder="Size"></td>
						<td><input type="text" name="projectHours" style="width:50px" placeholder="Hours"></td>
					</tr>
				</tbody>
			</table>
			<!--DESCRIPTION-->
			<textarea rows="3" cols="100" name="projectDesc" style="width:800px" form="projectForm" placeholder="Description"></textarea>
			<br>
			<input type="submit" name="submit" value="Add Project">
		</form>
		
		<?php
		if ($database)
			$database->close();
		?>
	</body>
</html>
