<?php
/*
	Faculty Profile
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
			Faculty Profile
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
			<li><a href="course.php">Course</a></li>
			<li><a href="section.php">Section</a></li>
			<li class="active"><a href="faculty.php">Faculty</a></li>
			<li><a href="partner.php">Partner</a></li>
			<li><a href="project.php">Project</a></li>
			<?php 
			if (phpCAS::isAuthenticated())
			{
				echo '<li><a>You are logged in as <font color="red">' . $casuser . '</font></li></a>';
				echo '<li><a href="?logout">(Logout)</li></a>';
			}
			else
				echo '<li><a href="login.php">Login</li></a>';
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
		$delComment= $_POST["delComment"];
		
		//Get posted variables for the edit page
		$facultyName			= $_POST["facultyName"];
		$facultyTraining		= $_POST["facultyTraining"];
		$facultyDesc			= $_POST["facultyDesc"];
		if ($_POST["facultyDeptText"])
			$facultyDept = $_POST["facultyDeptText"];
		else
			$facultyDept = $_POST["facultyDept"];
		
		//Remove the profile from the database if the Delete button is clicked on the edit page
		if($delete)
		{
			$return = $database->fullQuery("DELETE FROM faculty
											WHERE facultyID = '{$_POST["facultyID"]}';");
			unset($_POST['facultyID']);
		}
		
		//Get posted ID
		$facultyID= $_POST['facultyID'];
		
		//Update the profile when the Confirm button is clicked on the edit page
		if ($confirm)
		{
			if ($facultyDept != "NULL")
				$facultyDept = "'" . $facultyDept . "'";
				
			$return = $database->fullQuery("UPDATE faculty
											SET facultyName = '{$facultyName}',
												facultyDept = {$facultyDept},
												facultyTraining = {$facultyTraining},
												facultyDesc = '{$facultyDesc}',
												facultyCreator = '{$casuser}',
												facultyLastMod = '{$date}'
											WHERE facultyID= '{$facultyID}';");
		}
		
		//Add new comments
		if ($addComment)
		{
			$newComment = $_POST["newComment"];
			$return = $database->fullQuery("INSERT INTO facultyComments
											(facultyCom, facultyComCreator, facultyComLastMod, comFK_facultyID)
											VALUES ('{$newComment}', '{$casuser}', '{$date}', {$facultyID});");
		}

		//Delete old comments
		if ($delComment)
		{
			$return = $database->fullQuery("DELETE FROM facultyComments
											WHERE facultyComID = {$delComment};");
		}
		
		?>
		
		<h4>Faculty Profile</h4>
		
		<?
		
		//--------------------------------------------------
		//EDIT PAGE
		//--------------------------------------------------
		
		if ($edit)
		{
		?>
			<form action=faculty.php method=post>
				<input type="submit" name="confirm" value="Confirm">
				<input type="submit" name="cancel" value="Cancel"></td>
				<table class="table table-striped" style="width:30%">
					<thead>
						<tr>
							<th>Faculty Name</th>
							<th>Faculty Department</th>
							<th>Trained?</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<input type="text" style="width:120px" name="facultyName" size="10"
								placeholder="<?=$facultyName;?>" value="<?=$facultyName;?>">
							</td>
							<td>
								<select name="facultyDept">
									<option value="NULL">[None]</option>
									<?
									$result = $database->fullQuery("SELECT DISTINCT facultyDept AS dept FROM faculty
																	UNION
																	SELECT DISTINCT courseDept AS dept FROM course
																	ORDER BY dept");
									while ($row = mysqli_fetch_array($result))
									{
										$dept = $row["dept"];
										if ($dept != "")
										{
											if ($dept == $facultyDept)
												echo "<option value=\"{$dept}\" selected>{$dept}</option>";
											else
												echo "<option value=\"{$dept}\">{$dept}</option>";
										}
									}
									?>
								</select>
								<input type="text" name="facultyDeptText" placeholder="Add New">
							</td>
							<td>
								<select name="facultyTraining">
									<?
									if (!$facultyTraining)
										echo "<option value='NULL' selected>[Unknown]</option>";
									else
										echo "<option value='NULL'>[Unknown]</option>";
									if ($facultyTraining == "1")
										echo "<option value='1' selected>Yes</option>";
									else
										echo "<option value='1'>Yes</option>";
									if ($facultyTraining == "0")
										echo "<option value='0' selected>No</option>";
									else
										echo "<option value='0'>No</option>";
									?>
								</select>
							</td>
							<input type="hidden" name="facultyID" value="<?=$facultyID;?>">
						</tr>
					</tbody>
				</table>
				<b>Description</b>
				<br>
				<input type="text" name="facultyDesc" value="<?=$facultyDesc;?>"placeholder="<?=$facultyDesc;?>"style="width:1000px">
				<br>
				<br>
				<input type="submit" name="delete" value="Delete">
			</form>
		<?
		}
		
		//--------------------------------------------------
		//PROFILE PAGE
		//--------------------------------------------------
		
		else if ($facultyID)
		{
		?>
			<form action="faculty.php" method="POST">
				<input type="hidden" name="facultyID" value="">
				<input type="submit" name="profile" value="View All Faculty">
			</form>
			<hr style='background:#000000; border:0; height:3px' />
			<h4>Details</h4>
			Faculty ID = <?=$facultyID;?>
			<table class="table table-striped" style="width:30%">
				<thead>
					<tr>
						<th>Last Modified</th>
						<th>Modified By</th>
						<th>Name</th>
						<th>Department</th>
						<th>Trained?</th>
						<th>Edit</th>
					</tr>
				</thead>
				<tbody>
					<?
					$result = $database->fullQuery("SELECT * FROM faculty WHERE faculty.facultyID = {$facultyID};");
					$row = mysqli_fetch_array($result);
					if ($row["facultyTraining"] == "1")
						$facultyTrainWord = "Yes";
					else if ($row["facultyTraining"] == "0")
						$facultyTrainWord = "No";
					else
						$facultyTrainWord = "[Unknown]";
					?>
					<tr>
						<td><?=$row["facultyLastMod"];?></td>
						<td><?=$row["facultyCreator"];?></td>
						<td><?=$row["facultyName"];?></td>
						<?
						if ($row["facultyDept"])
							echo '<td>' . $row["facultyDept"] . '</td>';
						else
							echo '<td>[None]</td>';
						?>
						<td><?=$facultyTrainWord;?></td>
						<td>
							<form action="faculty.php" method="post">
								<input type="hidden" name="facultyID" value="<?=$row["facultyID"];?>">
								<input type="hidden" name="facultyLastMod" value="<?=$row["facultyLastMod"];?>">
								<input type="hidden" name="facultyCreator" value="<?=$row["facultyCreator"];?>">
								<input type="hidden" name="facultyName" value="<?=$row["facultyName"];?>">
								<input type="hidden" name="facultyDept" value="<?=$row["facultyDept"];?>">
								<input type="hidden" name="facultyTraining" value="<?=$row["facultyTraining"];?>">
								<input type="hidden" name="facultyDesc" value="<?=$row["facultyDesc"];?>">
								<input type="submit" name="edit" value="Edit">
							</form>
						</td>
					</tr>
				</tbody>
			</table>
			<br>
			<b>Description</b>
			<br>
			<?
			$facultyDesc = $row["facultyDesc"];
			if ((!$facultyDesc) || ($facultyDesc == ""))
				echo '[None]';
			else
				echo $facultyDesc;
			?>
			<br>
			<br>
			
			<hr style='background:#000000; border:0; height:3px' />
			<h4>Comments</h4>

			<table class="table table-striped" style="width:50%">
				<thead>
					<tr>
						<th>Comment</th>
						<th>Creator</th>
						<th>Time</th>
						<th>Delete</th>
					</tr>
				</thead>
				<tbody>
					<?
					$comQueryString ='SELECT * FROM facultyComments
									WHERE comFK_facultyID = ' . $facultyID . ';';
					$commentQuery = $database->multi_query($comQueryString) or die(mysql_error());
					$commentResult = $database->use_result();
					while ($row = mysqli_fetch_array($commentResult))
					{
						echo '<tr>';
						echo '<td>' . $row['facultyCom'] . '</td>';
						echo '<td>' . $row['facultyComCreator'] . '</td>';
						echo '<td>' . $row['facultyComLastMod'] . '</td>';
						echo '<form action="faculty.php" method=post>';
						echo '<input type="hidden" name="delComment" value ="'. $row['facultyComID'].'">';
						echo '<td><input type="submit" name="submit" value="Delete"></td>';
						echo '<input type="hidden" name="facultyID" value="'. $facultyID .'">';
						echo '</form>';
					}
					?>
				</tbody>
			</table>
			
			<form action="faculty.php" method=post>
				<td><input type="hidden" name="facultyID" value="<?=$facultyID;?>"></td>
				<td><input type="text" style="width:950px" name="newComment" placeholder="New Comment" value=""></td>
				<br>
				<td><input type="submit" name="addComment" value="Add Comment"></td>
			</form>
			
			<!-- Affiliated Sections List -->
			
			<hr style='background:#000000; border:0; height:3px' />
			<h4>Affiliated Sections</h4>
			<table class="table table-striped" style="width:30%">
				<thead>
					<th>Course</th>
					<th>Name</th>
					<th>Instructor</th>
					<th>Quarter</th>
					<th>Year</th>
					<th>Status</th>
					<th>CRN</th>
				</thead>
				<tbody>
					<?
						$result = $database->fullQuery("SELECT * FROM section
														LEFT JOIN course ON section.sectionFK_courseID = course.courseID
														LEFT JOIN faculty ON section.sectionFK_facultyID = faculty.facultyID
														WHERE facultyID = {$facultyID}
														ORDER BY sectionYear DESC,
														CASE
															WHEN sectionQuarter = 'Summer' THEN 1
															WHEN sectionQuarter = 'Spring' THEN 2
															WHEN sectionQuarter = 'Winter' THEN 3
															WHEN sectionQuarter = 'Fall' THEN 4
															ELSE 5
														END;");
						while ($row = mysqli_fetch_array($result))
						{
							echo "<tr>";
							echo "<td>" . $row["courseDept"] . $row["courseNum"] . "</td>";
							echo "<td>" . $row["courseName"] . "</td>";
							echo "<td>" . $row["facultyName"] . "</td>";
							echo "<td>" . $row["sectionQuarter"] . "</td>";
							echo "<td>" . $row["sectionYear"] . "</td>";
							echo "<td>" . $row["sectionStatus"] . "</td>";
							echo "<td>" . $row["sectionCRN"] . "</td>";
							echo "<td>
									<form action='section.php' method=post>
										<input type='hidden' name='sectionID' value={$row["sectionID"]}>
										<input type='submit' value='Details'>
									</form></td>";
							echo "</tr>";
						}
					?>
				</tbody>
			</table>
		<?
		}
		
		//--------------------------------------------------
		//LIST PAGE
		//--------------------------------------------------
		
		else //facultyID is not posted (!$facultyID)
		{
		?>
			<form action='export.php' method='post'>
				<button type='submit' name='export' value='SELECT * FROM faculty ORDER BY facultyName;'>Export All</button>
				<input type='hidden' name='outname' value='faculty'>
			</form>
			<table class="table table-striped" style="width:30%">
				<thead>
					<tr>
						<th>Name</th>
						<th>Department</th>
						<th>Trained?</th>
						<th>Details</th>
					</tr>
				</thead>
				<tbody>
					<?
					$result = $database->fullQuery("SELECT * FROM faculty
													ORDER BY facultyName;");
					while ($row = mysqli_fetch_array($result))
					{
						if ($row["facultyTraining"] == "1")
							$facultyTrainWord = "Yes";
						else if ($row["facultyTraining"] == "0")
							$facultyTrainWord = "No";
						else
							$facultyTrainWord = "";
					?>
					<tr>
						<td><?=$row["facultyName"];?></td>
						<td><?=$row["facultyDept"];?></td>
						<td><?=$facultyTrainWord;?></td>
						<td>
							<form action="faculty.php" method="post">
								<input type="hidden" name="facultyID" value=<?=$row["facultyID"];?>>
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