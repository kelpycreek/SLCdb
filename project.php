<?php
/*
	Project Profile
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
			Project Profile
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
			<li><a href="faculty.php">Faculty</a></li>
			<li><a href="partner.php">Partner</a></li>
			<li class="active"><a href="project.php">Project</a></li>
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
		$edit			= $_POST["edit"];
		$confirm		= $_POST["confirm"];
		$cancel			= $_POST["cancel"];
		$delete			= $_POST["delete"];
		$addComment		= $_POST["addComment"];
		$delComment		= $_POST["delComment"];
		
		//Get posted variables for the edit page
		$projectName			= $_POST["projectName"];
		$projectDesc			= $_POST["projectDesc"];
		$projectPartner			= $_POST["projectPartner"];
		$projectHours			= $_POST["projectHours"];
		$projectNumStudents		= $_POST["projectNumStudents"];
		$projectTimeSensitive	= $_POST["projectTimeSensitive"];
		$partnerName			= $_POST["partnerName"];
		$partnerID				= $_POST["partnerID"];
		if ($_POST["projectTypeText"])
			$projectType = $_POST["projectTypeText"];
		else
			$projectType = $_POST["projectType"];
		if ($_POST["projectStatusText"])
			$projectStatus = $_POST["projectStatusText"];
		else
			$projectStatus = $_POST["projectStatus"];
			
		//Remove the profile from the database if the Delete button is clicked on the edit page
		if($delete)
		{
			$pid = $_POST['projectID'];
			$query = "	DELETE FROM project
						WHERE projectID = {$pid};";
			$result = $database->multi_query($query);
			if (!$result)
			{
				echo ("Error: " . mysqli_error($database) . "<br><br>");
				echo ("The SQL query executed was:<br>" . $query);
			}
			else
			{
				echo ($projectName . " has been successfully deleted from the database<br>");
				unset($_POST['projectID']);
			}
			$query = "	DELETE FROM affiliate
						WHERE aff_projectID = {$pid};";
			$result = $database->multi_query($query);
			if (!$result)
			{
				echo ("Error: " . mysqli_error($database) . "<br><br>");
				echo ("The SQL query executed was:<br>" . $query);
			}
		}
		
		//Get posted ID
		$projectID = $_POST["projectID"];
		
		//Affiliate a new section with this project when the Add Section button is clicked
		if($_POST["addSection"])
		{
			$sectionID = $_POST["sectionID"];
	
			//Check if affiliation already exists
			$testres = $database->fullQuery("SELECT * FROM affiliate WHERE aff_projectID = {$projectID} AND aff_sectionID = {$sectionID};");
	
			//If return is False, then no rows were selected
			if (mysqli_num_rows($testres) == 0)
			{
				//Add new affiliation
				$query = "	INSERT INTO affiliate
									(aff_projectID, aff_sectionID)
							VALUES	({$projectID}, {$sectionID});";
			
				$return = $database->multi_query($query) or die("Error: " . mysqli_error($database) . "<br>");
	
				if (!$return)
				{
					echo ("Error: " . mysqli_error($database) . "<br>");
					return;
				}
				else
				{
					echo "The section was successfully added to this project.<br>";
					echo "<hr style='background:#000000; border:0; height:3px' />";
				}
			}
			else
			{
				echo "Error: That section has already been affiliated with this project...<br>";
				echo "<hr style='background:#000000; border:0; height:3px' />";
			}
		}

		//Remove an affiliated section from this project when the Delete button is clicked
		if($_POST["removeSection"])
		{
			$sectionID = $_POST["sectionID"];
			$query = "	DELETE FROM affiliate
						WHERE aff_projectID = {$projectID}
						AND aff_sectionID = {$sectionID};";
			$result = $database->multi_query($query);
	
			if (!$result)
			{
				echo ("Error: " . mysqli_error($database) . "<br>" . $query . "<br>");
				return;
			}
			else
			{
				echo "The section was successfully removed from this project.<br>";
				echo "<hr style='background:#000000; border:0; height:3px' />";
			}
		}
		
		//Update the profile when the Confirm button is clicked on the edit page
		if ($confirm)
		{
			$sectionLastMod = $date;
			$prevSectionCRN = $_POST["prevSectionCRN"];
			
			if ($projectType != "NULL") $projectType = "'" . $projectType . "'";
			
			if ($projectStatus != "NULL") $projectStatus = "'" . $projectStatus . "'";
			
			$query = sprintf("	UPDATE	project
								SET	projectLastMod='%s', 
									projectCreator='%s', 
									projectName='%s', 
									projectType=%s, 
									projectStatus=%s,
									projectFK_partnerID=%s,
									projectNumStudents='%s',
									projectHours='%s',
									projectTimeSensitive=%s,
									projectDesc='%s'
								WHERE	projectID='%s';",
								$date, $casuser, $projectName, $projectType, $projectStatus, $partnerID,
								$projectNumStudents, $projectHours, $projectTimeSensitive, $projectDesc, $projectID);
			$qry = $database->multi_query($query);
			
			if (!$qry)
			{
				echo ("Error: " . mysqli_error($database) . "<br>");
				echo $query;
				return;
			}

		}
		
		//Add new comments
		if ($addComment)
		{
			$newComment	= $_POST["newComment"];
			$return = $database->fullQuery("INSERT INTO projectComments
											(projectCom, projectComCreator, projectComLastMod, comFK_projectID)
											VALUES ('{$newComment}', '{$casuser}', '{$date}', {$projectID});");
		}

		//Delete old comments
		if ($delComment)
		{
			$return = $database->fullQuery("DELETE FROM projectComments
											WHERE projectComID = {$delComment};");
		}
		
		?>

		<h4>Project Profile</h4>
		
		<?
		
		//--------------------------------------------------
		//EDIT PAGE
		//--------------------------------------------------
		
		if ($edit)
		{
			?>
			<br>
			<form action=project.php method=post>
				<input type="submit" name="confirm" value="Confirm">
				<input type="submit" name="cancel" value="Cancel">
				<!--TOP ROW-->
				<table class="table table-striped" style="width:30%">
					<thead>
						<tr>
							<th>Project Name</th>
							<th>Project Type</th>
							<th>Project Status</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><input type="text" name="projectName" value = "<?=$projectName;?>" placeholder="<?=$projectName;?>"></td>
							<td>
								<select name="projectType">
									<option value="NULL">[None]</option>
										<?
										$result = $database->fullQuery("SELECT DISTINCT projectType FROM project
																		ORDER BY projectType");
										while ($row = mysqli_fetch_array($result))
										{
											$type = $row["projectType"];
											if ($type == $projectType && $type != "")
												echo "<option value=\"{$type}\" selected>{$type}</option>";
											else if ($type != "")
												echo "<option value=\"{$type}\">{$type}</option>";
										}
										?>
								</select>
								<input type="text" name="projectTypeText" placeholder="Add New">
							</td>
							<td>
								<select name="projectStatus">
									<option value="NULL">[None]</option>
									<?
									$result = $database->fullQuery("SELECT DISTINCT projectStatus FROM project
																	ORDER BY projectStatus;");
									while ($row = mysqli_fetch_array($result))
									{
										$stat = $row["projectStatus"];
										if ($stat == $projectStatus && $stat != "")
											echo "<option value=\"{$stat}\" selected>{$stat}</option>";
										else if ($stat != "")
											echo "<option value=\"{$stat}\">{$stat}</option>";
									}
									?>
								</select>
								<input type="text" name="projectStatusText" placeholder="Add New">
							</td>
						</tr>
					</tbody>
				</table>
				<!--BOTTOM ROW-->
				<table class="table table-striped" style="width:30%">
					<thead>
						<tr>
							<th>Project Partner</th>
							<th># of Students</th>
							<th># of Hours</th>
							<th>Time Sensitive?</th>
						<tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<select name="partnerID">
									<option value="NULL">[None]</option>
									<?php
										$result = $database->fullQuery("SELECT * FROM partner ORDER BY partnerName");
										while ($row = mysqli_fetch_array($result))
										{
											$pname = $row["partnerName"];
											$pid = $row["partnerID"];
											if ($pid == $partnerID)
												echo "<option value=\"{$pid}\" selected>{$pname}</option>";
											else
												echo "<option value=\"{$pid}\">{$pname}</option>";
										}
									?>
								</select>
							</td>
							<td>
								<input type="text" name="projectNumStudents" value="<?=$projectNumStudents;?>" 
								placeholder="<?=$projectNumStudents;?>" style="width:50px">
							</td>
							<td>
								<input type="text" name="projectHours" value="<?=$projectHours;?>" placeholder="<?=$projectHours;?>" style="width:50px">
							</td>
							<td>
								<select style="width:105px" name=projectTimeSensitive>
									<option value="NULL">[Unknown]</option>
									<?php
									if ($projectTimeSensitive == "1")
										echo '<option value=1 selected>Yes</option>';
									else
										echo '<option value=1>Yes</option>';	
									if ($projectTimeSensitive == "0")
										echo '<option value=0 selected>No</option>"';
									else
										echo '<option value=0>No</option>"';
									?>
								</select>
							</td>
							<td><input type="hidden" name="projectID" value="<?=$projectID;?>" placeholder="<?=$projectID;?>" style="width:50px"></td>
						</tr>
					</tbody>
				</table>
				<b>Description</b><br>
				<input type="text" name="projectDesc" value="<?=$projectDesc;?>" placeholder="<?=$projectDesc;?>" style="width:1000px"><br>
				<br>
				<input type="submit" name="delete" value="Delete">
			</form>
		<?
		}
		
		//--------------------------------------------------
		//PROFILE PAGE
		//--------------------------------------------------
		
		else if ($projectID)
		{
		?>
			<form action="project.php" method="POST">
				<input type="hidden" name="projectID" value="">
				<input type="submit" name="profile" value="View All Projects">
			</form>
			<hr style='background:#000000; border:0; height:3px' />
			<h4>Details</h4>
			Project ID = <?=$projectID;?>
			<table class="table table-striped" style="width:50%">
				<thead>
					<tr>
						<th>Last Modfied</th>
						<th>Modified By</th>
						<th>Name</th>
						<th>Partner</th>
						<th>Type</th>
						<th>Status</th>
						<th>Hours</th>
						<th>Students</th>
						<th>Time Sensitive?</th>
						<th>Edit</th>
					</tr>
				</thead>
				<tbody>
					<?
					$result = $database->fullQuery("SELECT * FROM project
													LEFT JOIN partner ON project.projectFK_partnerID = partner.partnerID
													WHERE project.projectID = {$projectID};");
					$row = mysqli_fetch_array($result);
					if ($row["projectTimeSensitive"] == "1")
						$projectTimeSensitive = 'Yes';
					else if ($row["projectTimeSensitive"] == "0")
						$projectTimeSensitive = 'No';
					else
						$projectTimeSensitive = '[Unknown]';
					?>
					<tr>
						<td><?=$row["projectLastMod"];?></td>
						<td><?=$row["projectCreator"];?></td>
						<td><?=$row["projectName"];?></td>
						
						<?
						if ($row["projectFK_partnerID"])
						{
							echo "<td><form action='partner.php' method=post>";
							echo "<input type='hidden' name='partnerID' value='{$row["projectFK_partnerID"]}'>";
							echo "<input type='submit' value='{$row["partnerName"]}'>";
							echo "</form></td>";
						}
						else
							echo '<td>[None]</td>';
					
						if ($row["projectType"])
							echo '<td>' . $row["projectType"] . '</td>';
						else
							echo '<td>[None]</td>';
					
						if ($row["projectStatus"])
							echo '<td>' . $row["projectStatus"] . '</td>';
						else
							echo '<td>[None]</td>';
						?>
						
						<td><?=$row["projectHours"];?></td>
						<td><?=$row["projectNumStudents"];?></td>
						<td><?=$projectTimeSensitive;?></td>
						<td>
							<form action="project.php" method="post">
								<input type="hidden" name="projectID" value="<?=$row["projectID"];?>">
								<input type="hidden" name="projectName" value="<?=$row["projectName"];?>">
								<input type="hidden" name="partnerName" value="<?=$row["partnerName"];?>">
								<input type="hidden" name="partnerID" value="<?=$row["projectFK_partnerID"];?>">
								<input type="hidden" name="projectType" value="<?=$row["projectType"];?>">
								<input type="hidden" name="projectStatus" value="<?=$row["projectStatus"];?>">
								<input type="hidden" name="projectHours" value="<?=$row["projectHours"];?>">
								<input type="hidden" name="projectNumStudents" value="<?=$row["projectNumStudents"];?>">
								<input type="hidden" name="projectTimeSensitive" value="<?=$row["projectTimeSensitive"];?>">
								<input type="hidden" name="projectDesc" value="<?=$row["projectDesc"];?>">
								<input type="submit" name="edit" value="Edit">
							</form>
						</td>
					</tr>
				</tbody>
			</table>
			<b>Description</b>
			<br>
			
			<?
			if ((!$projectDesc) || ($projectDesc == ""))
				echo '[None]';
			else
				echo $projectDesc;
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
					$commentResult = $database->fullQuery("	SELECT * FROM projectComments WHERE comFK_projectID = {$projectID};");
					while ($row = mysqli_fetch_array($commentResult))
					{
						echo '<tr>';
						echo '<td>' . $row['projectCom'] . '</td>';
						echo '<td>' . $row['projectComCreator'] . '</td>';
						echo '<td>' . $row['projectComLastMod'] . '</td>';
						echo '<form action="project.php" method=post><td>';
						echo '<input type="hidden" name="delComment" value ="'. $row['projectComID'].'">';
						echo '<input type="hidden" name="projectID" value="'. $projectID .'">';
						echo '<input type="submit" name="submit" value="Delete"></td>';
						echo '</form>';
					}
					?>
				</tbody>
			</table>
			<form action="project.php" method=post>
				<td><input type="hidden" name="projectID" value="<?=$projectID;?>"></td>
				<td><input type="text" style="width:1000px" name="newComment" placeholder="New Comment" value=""></td>
				<br>
				<td><input type="submit" name="addComment" value="Add"></td>
			</form>
			
			<hr style='background:#000000; border:0; height:3px' />
			
			<h4>Affiliated Sections</h4>
			
			<table class="table table-striped" style="width:50%">
				<thead>
					<tr>
						<th>Section</th>
						<th>Teacher</th>
						<th>CRN</th>
						<th>Quarter</th>
						<th>Year</th>
						<th>Status</th>
						<th>Delete</th>
					<tr>
				</thead>
				<tbody>
				
					<?
					//Select all affiliated section IDs
					$query = "SELECT * FROM affiliate WHERE aff_projectID = {$projectID};";
					$affQuery = $database->multi_query($query) or die("Error: " . mysqli_error($database) . "<br>");
					$affResult = $database->store_result();
				
					//Loop through affiliated IDs, selecting section data
					while ($row = mysqli_fetch_array($affResult))
					{
						$sectionResult = $database->fullQuery("	SELECT * from section
																LEFT JOIN course on section.sectionFK_courseID = courseID
																LEFT JOIN faculty on section.sectionFK_facultyID = facultyID
																WHERE sectionID = {$row["aff_sectionID"]};");
						$row2 = mysqli_fetch_array($sectionResult);
						echo "	<tr>
									<td>
										<form action='section.php' method=post>
											<input type='hidden' name='sectionID' value='{$row2["sectionID"]}'>
											<input type='submit' name='profile' value='{$row2["courseDept"]}{$row2["courseNum"]} - {$row2["courseName"]}'>
										</form>
									</td>
									<td>
										<form action='faculty.php' method=post>
											<input type='hidden' name='facultyID' value='{$row2["facultyID"]}'>
											<input type='submit' name='profile' value='{$row2["facultyName"]}'>
										</form>
									</td>
									<td>{$row2["sectionCRN"]}</td>
									<td>{$row2["sectionQuarter"]}</td>
									<td>{$row2["sectionYear"]}</td>
									<td>{$row2["sectionStatus"]}</td>";
						echo '		<form action="project.php" method=post>
										<td>
											<input type="hidden" name="sectionID" value="'. $row2["sectionID"] .'">
											<input type="hidden" name="projectID" value="'. $projectID .'">
											<input type="submit" name="removeSection" value="Delete">
										</td>
									</form>
								</tr>';
					}
					?>
				</tbody>
			</table>
			Add New Section:<br>
			<form action='project.php' method='post'>
				<select required name="sectionID">
					<option disabled selected>Select a section to add...</option>
					<?php
						$result = $database->fullQuery("SELECT * FROM section
														LEFT JOIN course on section.sectionFK_courseID = courseID
														ORDER BY sectionYear, sectionQuarter");
						while ($row = mysqli_fetch_array($result))
						{
							$cdept = $row["courseDept"];
							$cnum = $row["courseNum"];
							$cname = $row["courseName"];
							$sqtr = $row["sectionQuarter"];
							$syear = $row["sectionYear"];
							$sid = $row["sectionID"];
						
							if (!$row["partnerName"]) $pname = "[None]";
							else $pname = $row["partnerName"];
							$pid = $row["projectID"];
							echo "<option value=\"{$sid}\">{$cdept}{$cnum} - {$cname} ({$sqtr} {$syear})</option>";
						}
					?>
				</select>
				<input type='hidden' name='projectID' value='<?=$projectID;?>'>
				<input type='submit' name='addSection' value='Add'>
			</form>
		<?
		}
		
		//--------------------------------------------------
		//LIST PAGE
		//--------------------------------------------------
		
		else
		{
		?>
			<form action="export.php" method="post">
				<button type="submit" name="export" value="	SELECT * FROM project
															LEFT JOIN partner ON project.projectFK_partnerID = partner.partnerID
															ORDER BY partnerName;">Export All</button>
				<input type="hidden" name="outname" value="project">
			</form>
			<table class="table table-striped" style="width:50%">
				<thead>
					<tr>
						<th>Name</th>
						<th>Partner</th>
						<th>Type</th>
						<th>Status</th>
						<th>Hours</th>
						<th>Students</th>
						<th>Time Sensitive?</th>
						<th>Details</th>
					</tr>
				</thead>
				<tbody>
					<?
					$result = $database->fullQuery("SELECT * FROM project
													LEFT JOIN partner ON project.projectFK_partnerID = partner.partnerID
													ORDER BY partnerName;");
					while ($row = mysqli_fetch_array($result))
					{
						if ($row["projectTimeSensitive"] == "1")
							$projectTimeSensitive = 'Yes';
						else if ($row["projectTimeSensitive"] == "0")
							$projectTimeSensitive = 'No';
						else
							$projectTimeSensitive = '';
							
					?>
						<tr>
							<td><?=$row["projectName"];?></td>
							<?
							if ($row["projectFK_partnerID"])
							{
								echo '<td><form action="partner.php" method=post>';
								echo sprintf('<input type="hidden" name="partnerID" value="%s">', $row["projectFK_partnerID"]);
								echo '<input type="submit" value="'.$row["partnerName"].'">';
								echo '</form></td>';
							}
							else
								echo '<td></td>';
							?>
							<td><?=$row["projectType"];?></td>
							<td><?=$row["projectStatus"];?></td>
							<td><?=$row["projectHours"];?></td>
							<td><?=$row["projectNumStudents"];?></td>
							<td><?=$projectTimeSensitive;?></td>
							<td>
								<form action="project.php" method=post>
									<input type="hidden" name="projectID" value="<?=$row["projectID"];?>">
									<input type="submit" value="Details">
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
