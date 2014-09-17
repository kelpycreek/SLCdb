<?php
/*
	Course Profile
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
		<title>
			Course Profile
		</title>
		<link href="bootstrap.css" rel="stylesheet">
	</head>
	<body>
		<img border="0" src="banner.jpg" width="100%" height="150">
		<link href="bootstrap.css" rel="stylesheet">
		<ul class="nav nav-pills">
			<li><a href="main.php">Home</a></li>
			<li><a href="info.php">Info</a></li>
			<li><a href="add.php">Add</a></li>
			<li><a href="report.php">Report</a></li>
			<li class="active"><a href="course.php">Course</a></li>
			<li><a href="section.php">Section</a></li>
			<li><a href="faculty.php">Faculty</a></li>
			<li><a href="partner.php">Partner</a></li>
			<li><a href="project.php">Project</a></li>
			<?php 
			if (phpCAS::isAuthenticated())
			{
				echo '<li><a>You are logged in as <font color="red">' . $casuser . '</font></li></a>';
				echo '<li><a href="?logout">(Logout)</li></a>';
			}
			else echo '<li><a href="login.php">Login</li></a>';
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
		
		//Get the date and time
		$time = time();
		$format = "n/j/y g:ia";
		$dateFormat = new DateTime(date($format, $time));
		$date = $dateFormat->format($format);
		
		//Determine which actions to take on this page
		$edit= $_POST["edit"];
		$confirm= $_POST["confirm"];
		$cancel= $_POST["cancel"];
		$delete= $_POST["delete"];
		$addComment= $_POST["addComment"];
		$newComment= $_POST["newComment"];
		$delComment= $_POST["delComment"];
		
		//Get posted variables for the edit page
		$courseNum			= $_POST['courseNum'];
		$courseName			= $_POST['courseName'];
		$courseDesc			= $_POST['courseDesc'];
		$courseCreator		= $_POST['courseCreator'];
		$courseLastMod		= $_POST['courseLastMod'];
		if ($_POST['courseDeptText'])
			$courseDept = $_POST['courseDeptText'];
		else
			$courseDept = $_POST['courseDept'];
		
		//Remove the profile from the database if the Delete button is clicked on the edit page
		if($delete)
		{
			$return = $database->fullQuery("DELETE FROM course
											WHERE courseID = {$courseID};");
			unset($_POST['courseID']);
		}
		
		//Get posted ID
		$courseID = $_POST['courseID'];

		//Update the profile when the Confirm button is clicked on the edit page
		if ($confirm)
		{
			if ($courseDept != "NULL")
				$courseDept = "'" . $courseDept . "'";
				
			$return = $database->fullQuery("UPDATE	course
											SET	courseNum = '{$courseNum}',
												courseDept = {$courseDept},
												courseName = '{$courseName}',
												courseDesc = '{$courseDesc}',
												courseCreator = '{$casuser}',
												courseLastMod = '{$date}'
											WHERE courseID = '{$courseID}';");
		}
		
		?>

		<h4>Course Profile</h4>
		
		<?
		
		//--------------------------------------------------
		//EDIT PAGE
		//--------------------------------------------------
		
		if ($edit)
		{
		?>
			<form action=course.php method=post>
				<input type="submit" name="confirm" value="Confirm">
				<input type="submit" name="cancel" value="Cancel">
				<table class="table table-striped" style="width:30%%">
					<thead>
						<tr>
							<th>Department</th>
							<th>Number</th>
							<th>Name</th>
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
											if ($dept && $dept == $courseDept)
											{
												echo "<option value=\"{$dept}\" selected>{$dept}</option>";
											}
											else if ($dept != "")
											{
												echo "<option value=\"{$dept}\">{$dept}</option>";
											}
										}
									?>
								</select>
								<input type="text" name="courseDeptText" placeholder="Add New">
							</td>
							<td><input type="text" style="width:120px" name="courseNum" size="10" placeholder="<?=$courseNum;?>" value="<?=$courseNum;?>"></td>
							<td><input type="text" style="width:120px" name="courseName" size="10" placeholder="<?=$courseName;?>" value="<?=$courseName;?>"></td>
							<input type="hidden" name="courseID" value="<?=$courseID;?>">
						</tr>
					</tbody>
				</table>
				<b>Description</b>
				<br>
				<input type="text" name="courseDesc" value="<?=$courseDesc;?>" placeholder="<?=$courseDesc;?>" style="width:1000px">
				<br>
				<br>
				<input type="submit" name="delete" value="Delete">
			</form>
		<?
		}
		
		//--------------------------------------------------
		//PROFILE PAGE
		//--------------------------------------------------
		
		else if ($courseID)
		{
			$result = $database->fullQuery("SELECT * FROM course 
											WHERE courseID = {$courseID};");
			$row = mysqli_fetch_array($result);
		?>
			<form action="course.php" method="POST">
				<input type="hidden" name="courseID" value="">
				<input type="submit" name="profile" value="View All Courses">
			</form>
			<hr style='background:#000000; border:0; height:3px' />
			<h4>Details</h4>
			Course ID = <?=$courseID;?>
			<table class="table table-striped" style="width:30%">
				<thead>
					<tr>
						<th>Last Modified</th>
						<th>Modified By</th>
						<th>Department</th>
						<th>Number</th>
						<th>Name</th>
						<th>Edit</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?=$row["courseLastMod"];?></td>
						<td><?=$row["courseCreator"];?></td>
						
						<?
						//Department
						if ($row["courseDept"])
							echo '<td>' . $row["courseDept"] . '</td>';
						else
							echo '<td>[None]</td>';
							
						//Number
						if ($row["courseNum"])
							echo '<td>' . $row["courseNum"] . '</td>';
						else
							echo '<td>[None]</td>';
							
						//Name
						if ($row["courseName"])
							echo '<td>' . $row["courseName"] . '</td>';
						else
							echo '<td>[None]</td>';
							
						//Edit button
						?>
						<form action="course.php" method="POST">
							<td>
								<input type="hidden" name="courseID" value="<?=$row["courseID"];?>">
								<input type="hidden" name="courseDept" value="<?=$row["courseDept"];?>">
								<input type="hidden" name="courseNum" value="<?=$row["courseNum"];?>">
								<input type="hidden" name="courseName" value="<?=$row["courseName"];?>">
								<input type="hidden" name="courseDesc" value="<?=$row["courseDesc"];?>">
								<input type="submit" name="edit" value="Edit">
							</td>
						</form>

					</tr>
				</tbody>
			</table>
			<br>
			<b>Description</b>
			<br>
			<?
			$courseDesc = $row["courseDesc"];
			if ((!$courseDesc) || ($courseDesc == ""))
				echo '[None]';
			else
				echo $courseDesc . "<br><br>";
		}
		
		//--------------------------------------------------
		//LIST PAGE
		//--------------------------------------------------
		
		else //courseID is not posted (!$courseID)
		{
			?>
			<form action='export.php' method='post'>
				<button type='submit' name='export' value='SELECT * FROM course ORDER BY courseDept, courseNum;'>Export All</button>
				<input type='hidden' name='outname' value='course'>
			</form>
			<table class="table table-striped" style="width:30%">
				<thead>
					<tr>
						<th>Department</th>
						<th>Number</th>
						<th>Name</th>
						<th>Details</th>
					</tr>
				</thead>
				<tbody>
			
			<?
			$result = $database->fullQuery("SELECT * FROM course
											ORDER BY courseDept, courseNum;");
			while ($row = mysqli_fetch_array($result))
			{
			?>
					<tr>
						<td><?=$row["courseDept"];?></td>
						<td><?=$row["courseNum"];?></td>
						<td><?=$row["courseName"];?></td>
						<td>
							<form action="course.php" method="post">
								<input type="hidden" name="courseID" value="<?=$row["courseID"];?>">
								<input type="submit" name="profile" value="Details">
							</form>
						</td>
					</tr>
			<?
			}
			?>
				</tbody>
			</table>
		<?
		}
		
		if ($database)
			$database->close();
		?>
	</body>
</html>
