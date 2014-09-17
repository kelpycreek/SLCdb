<?php
	/*
		Section Profile
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
			Section Profile
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
			<li class="active"><a href="section.php">Section</a></li>
			<li><a href="faculty.php">Faculty</a></li>
			<li><a href="partner.php">Partner</a></li>
			<li><a href="project.php">Project</a></li>
			<?php 
			if(phpCAS::isAuthenticated())
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
		
		//Get the date and time
		$time = time();
		$format = "n/j/y g:ia";
		$dateFormat = new DateTime(date($format, $time));
		$date = $dateFormat->format($format);
		
		//Connect to the SLC database
		$database = connectSLC();
		
		//Determine which actions to take on this page
		$edit			= $_POST["edit"];
		$confirm		= $_POST["confirm"];
		$cancel			= $_POST["cancel"];
		$delete			= $_POST["delete"];
		$addComment		= $_POST["addComment"];
		$delComment		= $_POST["delComment"];
		
		//Get posted variables for the edit page
		$sectionCRN			= $_POST["sectionCRN"];
		$sectionQuarter		= $_POST["sectionQuarter"];
		$sectionYear		= $_POST["sectionYear"];
		$sectionNumStudents	= $_POST["sectionNumStudents"];
		$sectionSL			= $_POST["sectionSL"];
		$sectionRefl		= $_POST["sectionRefl"];
		$sectionEval		= $_POST["sectionEval"];
		$sectionPres		= $_POST["sectionPres"];
		$sectionDesc		= $_POST["sectionDesc"];
		$sectionCreator		= $_POST["sectionCreator"];
		$sectionLastMod		= $_POST["sectionLastMod"];
		$courseID			= $_POST["courseID"];
		$courseDept			= $_POST["courseDept"];
		$courseNum			= $_POST["courseNum"];
		$facultyID			= $_POST["facultyID"];
		$facultyName		= $_POST["facultyName"];
		$sectionStatus		= $_POST["sectionStatusText"] ? $_POST["sectionStatusText"] : $_POST["sectionStatus"];

		//Remove the profile from the database if the Delete button is clicked on the edit page
		if($delete)
		{
			//Delete the section
			$sid = $_POST['sectionID'];
			$return = $database->fullQuery("DELETE FROM section
											WHERE sectionID = {$sid};");
			echo ("The section has been successfully deleted from the database<br>");
			unset($_POST['sectionID']);
			
			//Delete any affiliations with this section
			$return = $database->fullQuery("DELETE FROM affiliate
											WHERE aff_sectionID = {$sid};");
		}
		
		//Get posted ID
		$sectionID = $_POST["sectionID"];

		//Affiliate a new project with this section when the Add Project button is clicked
		if($_POST["addProject"])
		{
			$projectID = $_POST["projectID"];
	
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
					echo "The project was successfully added to this section.<br>";
					echo "<hr style='background:#000000; border:0; height:3px' />";
				}
			}
			else
			{
				echo "Error: That project has already been affiliated with this section...<br>";
				echo "<hr style='background:#000000; border:0; height:3px' />";
			}
		}

		//Remove an affiliated project from this section when the Delete button is clicked
		if($_POST["removeProject"])
		{
			$projectID = $_POST["projectID"];
				
			$result = $database->fullQuery("DELETE FROM affiliate
											WHERE aff_projectID = {$projectID}
											AND aff_sectionID = {$sectionID};");
	
			echo "The project was successfully removed from this section.<br>";
			echo "<hr style='background:#000000; border:0; height:3px' />";
		}

		//Update the profile when the Confirm button is clicked on the edit page
		if ($confirm)
		{
			$sectionLastMod = $date;
			$sectionCreator = $casuser;
	
			if ($courseID == "")
				$courseID = 'NULL';
	
			if ($facultyID == "")
				$facultyID = 'NULL';
			
			if ($sectionQuarter != "NULL")
				$sectionQuarter = "'" . $sectionQuarter . "'";
			
			if ($sectionStatus != "NULL")
				$sectionStatus = "'" . $sectionStatus . "'";
			
			$query = "UPDATE section SET sectionLastMod='{$sectionLastMod}', 
									sectionCreator='{$sectionCreator}', 
									sectionCRN='{$sectionCRN}', 
									sectionFK_courseID={$courseID}, 
									sectionFK_facultyID={$facultyID},
									sectionQuarter={$sectionQuarter},
									sectionYear='{$sectionYear}',
									sectionStatus={$sectionStatus},
									sectionNumStudents='{$sectionNumStudents}',
									sectionSL={$sectionSL},
									sectionRefl={$sectionRefl},
									sectionEval={$sectionEval},
									sectionPres={$sectionPres},
									sectionDesc={$sectionDesc}
									WHERE sectionID={$sectionID};";
									
			$result = $database->fullQuery($query);
		}
		
		//Add new comments
		if ($addComment)
		{
			$newComment		= $_POST["newComment"];
			$result = $database->fullQuery("INSERT INTO sectionComments
											(sectionCom, sectionComCreator, sectionComLastMod, comFK_sectionID)
											VALUES ('{$newComment}', '{$casuser}', '{$date}', {$sectionID});");
		}

		//Delete old comments
		if ($delComment)
		{
			$result = $database->fullQuery("DELETE FROM sectionComments
											WHERE sectionComID = {$delComment};");
		}
		
		?>
		
		<h4>Section Profile</h4>
		
		<?
	
		//--------------------------------------------------
		//EDIT PAGE
		//--------------------------------------------------

		//Edit form
		if ($edit)
		{
			?>
			<form action="section.php" method="POST">
				<!--Confirm and Cancel buttons-->
				<input type="submit" name="confirm" value="Confirm">
				<input type="submit" name="cancel" value="Cancel"></td>
				
				<!--TOP ROW-->
			
				<table class="table table-striped" style="width:30%">
					<thead>
						<tr>
							<th>Course</th>
							<th>Instructor</th>
							<th>Quarter</th>
							<th>Year</th>
							<th>Status</th>
							<th>CRN</th>
							<th>Students</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<!--Edit Course-->
							<td>
								<select name="courseID">
								<option value="NULL">[None]</option>
								<?php
									$result = $database->fullQuery("SELECT * FROM course
																	ORDER BY courseDept, courseNum");
									while ($row = mysqli_fetch_array($result))
									{
										$name = $row["courseName"];
										$dept = $row["courseDept"];
										$num = $row["courseNum"];
										$cid = $row["courseID"];
										if ($courseID == $cid) echo "<option value=\"{$cid}\" selected>{$dept}{$num} - {$name}</option>";
										else echo "<option value=\"{$cid}\">{$dept}{$num} - {$name}</option>";
									}
								?>
							</select>
							</td>
							<!--Edit Instructor-->
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
											if ($fid == $facultyID)
												echo "<option value=\"{$fid}\" selected>{$name} ({$dept})</option>";
											else
												echo "<option value=\"{$fid}\">{$name} ({$dept})</option>";
										}
									?>
								</select>
							</td>
							<!--Edit Quarter-->
							<td>
								<select name="sectionQuarter">
									<?php
										if (!$sectionQuarter)
											echo "<option value='NULL' selected>[None]</option>";
										else
											echo "<option value='NULL'>[None]</option>";
										if ($sectionQuarter == "Spring")
											echo "<option value='Spring' selected>Spring</option>";
										else
											echo "<option value='Spring'>Spring</option>";
										if ($sectionQuarter == "Summer")
											echo "<option value='Summer' selected>Summer</option>";
										else
											echo "<option value='Summer'>Summer</option>";
										if ($sectionQuarter == "Fall")
											echo "<option value='Fall' selected>Fall</option>";
										else
											echo "<option value='Fall'>Fall</option>";
										if ($sectionQuarter == "Winter")
											echo "<option value='Winter' selected>Winter</option>";
										else
											echo "<option value='Winter'>Winter</option>";
									?>
								</select>
							</td>
							<!--Edit Year-->
							<td>
								<input type="text" style="width:50px" name="sectionYear" placeholder="<?=$sectionYear;?>" value="<?=$sectionYear;?>">
							</td>
							<!--Edit Status-->
							<td>
								<select name="sectionStatus">
									<option value="NULL">[None]</option>
									<?php
										$result = $database->fullQuery("SELECT DISTINCT sectionStatus
																		FROM section
																		ORDER BY sectionStatus");
										while ($row = mysqli_fetch_array($result))
										{
											$stat = $row["sectionStatus"];
											if ($stat == $sectionStatus && $stat != "")
												echo "<option value=\"{$stat}\" selected>{$stat}</option>";
											else if ($stat != "")
												echo "<option value=\"{$stat}\">{$stat}</option>";
										}
									?>
								</select>
								<input type="text" name="sectionStatusText" placeholder="Add New">
							</td>
							<!--Edit CRN-->
							<td>
								<input type="text" style="width:50px" name="sectionCRN" size="10" placeholder="<?=$sectionCRN;?>" value="<?=$sectionCRN;?>">
							</td>
							<!--Edit NumStudents-->
							<td>
								<input type="text" style="width:50px" name="sectionNumStudents" placeholder="<?=$sectionNumStudents;?>" value="<?=$sectionNumStudents;?>">
							</td>
						</tr>
					</tbody>
				</table>
			
				<!--BOTTOM ROW-->
			
				<table class="table table-striped" style="width:30%">
					<thead>
						<tr>
							<th>SL Attribute?</th>
							<th>Reflections?</th>
							<th>Evaluations?</th>
							<th>Presentations?</th>
							<th>Description Sheet?</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<!--Edit SL Attribute-->
							<td>
								<select style="width:105px" name="sectionSL">
									<?
										if (!$sectionSL) echo "<option value=\"NULL\" selected>[Unknown]</option>";
										else echo "<option value=\"NULL\">[Unknown]</option>";
										if ($sectionSL == "1") echo "<option value=1 selected>Yes</option>";
										else echo "<option value=1>Yes</option>";
										if ($sectionSL == "0") echo "<option value=0 selected>No</option>";
										else echo "<option value=0>No</option>";
									?>
								</select>
							</td>
							<!--Edit Reflections-->
							<td>
								<select style="width:105px" name="sectionRefl">
									<?
										if (!$sectionRefl) echo "<option value=\"NULL\" selected>[Unknown]</option>";
										else echo "<option value=\"NULL\">[Unknown]</option>";
										if ($sectionRefl == "1") echo "<option value=1 selected>Yes</option>";
										else echo "<option value=1>Yes</option>";
										if ($sectionRefl == "0") echo "<option value=0 selected>No</option>";
										else echo "<option value=0>No</option>";
									?>
								</select>
							</td>
							<!--Edit Evaluations-->
							<td>
								<select style="width:105px" name="sectionEval">
									<?
										if (!$sectionEval) echo "<option value=\"NULL\" selected>[Unknown]</option>";
										else echo "<option value=\"NULL\">[Unknown]</option>";
										if ($sectionEval == "1") echo "<option value=1 selected>Yes</option>";
										else echo "<option value=1>Yes</option>";
										if ($sectionEval == "0") echo "<option value=0 selected>No</option>";
										else echo "<option value=0>No</option>";
									?>
								</select>
							</td>
							<!--Edit Presentations-->
							<td>
								<select style="width:105px" name="sectionPres">
									<?
										if (!$sectionPres) echo "<option value=\"NULL\" selected>[Unknown]</option>";
										else echo "<option value=\"NULL\">[Unknown]</option>";
										if ($sectionPres == "1") echo "<option value=1 selected>Yes</option>";
										else echo "<option value=1>Yes</option>";
										if ($sectionPres == "0") echo "<option value=0 selected>No</option>";
										else echo "<option value=0>No</option>";
									?>
								</select>
							</td>
							<!--Edit Descriptions-->
							<td>
								<select style="width:105px" name="sectionDesc">
									<?
										if (!$sectionDesc) echo "<option value=\"NULL\" selected>[Unknown]</option>";
										else echo "<option value=\"NULL\">[Unknown]</option>";
										if ($sectionDesc == "1") echo "<option value=1 selected>Yes</option>";
										else echo "<option value=1>Yes</option>";
										if ($sectionDesc == "0") echo "<option value=0 selected>No</option>";
										else echo "<option value=0>No</option>";
									?>
								</select>
							</td>
							<!--Post ID details-->
							<input type="hidden" name="profile" value="Section">
							<input type="hidden" name="sectionID" value="<?=$sectionID;?>">
						</tr>
					</tbody>
				</table>
				<!--Delete button-->
				<input type="submit" name="delete" value="Delete">
			</form>
		<? }
	
		//==================================================
		//PROFILE PAGE
		//==================================================
	
		else if ($sectionID)
		{
			$result = $database->fullQuery("SELECT * FROM section
											LEFT JOIN course ON section.sectionFK_courseID = course.courseID
											LEFT JOIN faculty ON section.sectionFK_facultyID = faculty.facultyID
											WHERE section.sectionID = {$sectionID};");
			$row = mysqli_fetch_array($result);
			?>
			
			<form action="section.php" method="POST">
				<input type="hidden" name="sectionID" value="">
				<input type="submit" name="profile" value="View All Sections">
			</form>
			<hr style='background:#000000; border:0; height:3px' />
			<h4>Details</h4>
			Section ID = <?=$sectionID;?>
			<table class="table table-striped" style="width:50%">
				<thead>
					<tr>
						<th>Last Modfied</th>
						<th>Modified By</th>
						<th>Course</th>
						<th>Name</th>
						<th>Instructor</th>
						<th>Quarter</th>
						<th>Year</th>
						<th>Status</th>
						<th>CRN</th>
						<th>Students</th>
						<th>Edit</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						
						<td><?=$row["sectionLastMod"]?></td>
						<td><?=$row["sectionCreator"]?></td>
						
						<?
						//Course Department / Course Number
						if (($row["courseDept"] || $row["courseNum"]))
							echo "<td>{$row["courseDept"]}{$row["courseNum"]}</td>";
						else
							echo '<td>[None]</td>';
							
						//Course Name
						if ($row["courseName"])
							echo "<td>{$row["courseName"]}</td>";
						else
							echo "<td>[None]</td>";
						
						//Faculty Button
						if ($row["sectionFK_facultyID"])
						{ ?>
							<td>
								<form action="faculty.php" method="post">
									<input type="hidden" name="facultyID" value="<?=$row['sectionFK_facultyID'];?>">
									<input type="submit" name="profile" value="<?=$row['facultyName'];?>">
								</form>
							</td>
						<? }
						else
							echo "<td>[None]</td>";
						
						//Quarter
						if (!$row["sectionQuarter"])
							echo "<td>[None]</td>";
						else
							echo '<td>' . $row["sectionQuarter"] . '</td>';
							
						//Year
						echo '<td>' . $row["sectionYear"] . '</td>';
						
						//Status
						if (!$row["sectionStatus"])
							echo "<td>[None]</td>";
						else
							echo '<td>' . $row["sectionStatus"] . '</td>';
						
						//CRN
						if ((!$row["sectionCRN"]) || ($row["sectionCRN"] == ""))
							echo '<td>[None]</td>';
						else
							echo "<td>{$row["sectionCRN"]}</td>";
						
						//Number of Students
						echo "<td>{$row["sectionNumStudents"]}</td>";
		
						//Edit button
						?>
						<td>
							<form action="section.php" method="post">
								<input type="hidden" name="sectionID" value="<?=$row["sectionID"];?>">
								<input type="hidden" name="sectionLastMod" value="<?=$row["sectionLastMod"];?>">
								<input type="hidden" name="sectionCreator" value="<?=$row["sectionCreator"];?>">
								<input type="hidden" name="sectionCRN" value="<?=$row["sectionCRN"];?>">
								<input type="hidden" name="courseID" value="<?=$row["courseID"];?>">
								<input type="hidden" name="courseDept" value="<?=$row["courseDept"];?>">
								<input type="hidden" name="courseNum" value="<?=$row["courseNum"];?>">
								<input type="hidden" name="facultyName" value="<?=$row["facultyName"];?>">
								<input type="hidden" name="facultyID" value="<?=$row["facultyID"];?>">
								<input type="hidden" name="sectionQuarter" value="<?=$row["sectionQuarter"];?>">
								<input type="hidden" name="sectionYear" value="<?=$row["sectionYear"];?>">
								<input type="hidden" name="sectionStatus" value="<?=$row["sectionStatus"];?>">
								<input type="hidden" name="sectionNumStudents" value="<?=$row["sectionNumStudents"];?>">
								<input type="hidden" name="sectionSL" value="<?=$row["sectionSL"];?>">
								<input type="hidden" name="sectionRefl" value="<?=$row["sectionRefl"];?>">
								<input type="hidden" name="sectionEval" value="<?=$row["sectionEval"];?>">
								<input type="hidden" name="sectionPres" value="<?=$row["sectionPres"];?>">
								<input type="hidden" name="sectionDesc" value="<?=$row["sectionDesc"];?>">
								<input type="submit" name="edit" value="Edit">
							</form>
						</td>
					</tr>
				</tbody>
			</table>
			
			<table class="table table-striped" style="width:50%">
				<thead>
					<tr>
						<th>SL Attribute?</th>
						<th>Reflections?</th>
						<th>Evaluations?</th>
						<th>Presentations?</th>
						<th>Description Sheet?</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<?
						//Print bits as "Yes/No" strings
						//SL Attribute
						if ($row["sectionSL"] == "1")
							echo "<td>Yes</td>";
						else if ($row["sectionSL"] == "0")
							echo "<td>No</td>";
						else
							echo "<td>[Unknown]</td>";
						//Reflections
						if ($row["sectionRefl"] == "1")
							echo "<td>Yes</td>";
						else if ($row["sectionRefl"] == "0")
							echo "<td>No</td>";
						else
							echo "<td>[Unknown]</td>";
						//Evaluations
						if ($row["sectionEval"] == "1")
							echo "<td>Yes</td>";
						else if ($row["sectionEval"] == "0")
							echo "<td>No</td>";
						else
							echo "<td>[Unknown]</td>";
						//Presentations
						if ($row["sectionPres"] == "1")
							echo "<td>Yes</td>";
						else if ($row["sectionPres"] == "0")
							echo "<td>No</td>";
						else
							echo "<td>[Unknown]</td>";
						//Description Sheet
						if ($row["sectionDesc"] == "1")
							echo "<td>Yes</td>";
						else if ($row["sectionDesc"] == "0")
							echo "<td>No</td>";
						else
							echo "<td>[Unknown]</td>";
						?>
					</tr>
				</tbody>
			</table>
			<hr style='background:#000000; border:0; height:3px' />
			
			<!--COMMENT LIST-->

			<table class="table table-striped" style="width:50%">
			<h4>Comments</h4>
				<thead>
					<tr>
						<th>Comment</th>
						<th>Creator</th>
						<th>Time</th>
						<th>Delete</th>
					</tr>
				</thead>
				<tbody>
					<form action="section.php" method=post>
					<?
						$result = $database->fullQuery("SELECT * FROM sectionComments
														WHERE comFK_sectionID = {$sectionID};");
						while ($row = mysqli_fetch_array($result))
						{ ?>
							<tr>
							<td><?=$row["sectionCom"];?></td>
							<td><?=$row["sectionComCreator"];?></td>
							<td><?=$row["sectionComLastMod"];?></td>
							<td><button type="submit" name="delComment" value="<?=$row["sectionComID"]?>">Delete</button></td>
						<? } ?>

						<td><input type="hidden" name="sectionID" value="<?=$sectionID;?>"></td>
					</form>
				</tbody>
			</table>
		
			<form action="section.php" method=post>
				<td><input type="hidden" name="sectionID" value="<?=$sectionID?>"></td>
				<td><input type="text" style="width:950px" name="newComment" placeholder="New Comment" value=""></td>
				<br>
				<td><input type="submit" name="addComment" value="Add"></td>
			</form>
		
			<!--Affiliated Project List-->
		
			<hr style='background:#000000; border:0; height:3px' />
			<h4>Affiliated Projects</h4>
		
			<table class="table table-striped" style="width:50%"">
			<thead>
				<tr>
					<th>Project Name</th>
					<th>Partner Name</th>
					<th>Project Type</th>
					<th>Project Status</th>
					<th>Delete</th>
				<tr>
			</thead>
			<tbody>
			
			<?php
			//Select all affiliated project IDs
			$affResult = $database->fullQuery("SELECT * FROM affiliate WHERE aff_sectionID = {$sectionID};");
			
			//Loop through affiliated IDs, selecting project data
			while ($row = mysqli_fetch_array($affResult))
			{
				$projectResult = $database->fullQuery("	SELECT * from project
														LEFT JOIN partner on project.projectFK_partnerID = partner.partnerID
														WHERE projectID = {$row["aff_projectID"]};");
				$row2 = mysqli_fetch_array($projectResult);
			?>
			<tr>
				<td>
					<form action="project.php" method="POST">
						<input type="hidden" name="projectID" value="<?=$row2['projectID'];?>">
						<input type='submit' name='profile' value="<?=$row2['projectName'];?>">
					</form>
				</td>
				<td>
					<?
					if ($row2['partnerName'] != "")
					{ ?>
					<form action="partner.php" method="POST">
						<input type="hidden" name="partnerID" value="<?=$row2['partnerID'];?>">
						<input type='submit' name='profile' value="<?=$row2['partnerName'];?>">
					</form>
					<? } ?>
				</td>
				<td><?=$row2["projectType"];?></td>
				<td><?=$row2["projectStatus"];?></td>
				<form action="section.php" method=post>
					<td>
						<input type="hidden" name="projectID" value="<?=$row2['projectID'];?>">
						<input type="hidden" name="sectionID" value="<?=$sectionID;?>">
						<input type="submit" name="removeProject" value="Delete">
					</td>
				</form>
			</tr>
			<?
			}
			?>
			
			</tbody>
		</table>
		
		<!--Affiliate new project list-->
		Add New Projects:<br>
		<form action='section.php' method='post'>
			<select required name="projectID">
				<option disabled selected>Select a project to add...</option>
				<?php
					$result = $database->fullQuery("SELECT * FROM project
													LEFT JOIN partner on project.projectFK_partnerID = partnerID
													ORDER BY projectName");
					while ($row = mysqli_fetch_array($result))
					{
						$name = $row["projectName"];
						if (!$row["partnerName"]) $pname = "[None]";
						else $pname = $row["partnerName"];
						$pid = $row["projectID"];
						echo "<option value=\"{$pid}\">{$name} ({$pname})</option>";
					}
				?>
			</select>
			<input type='hidden' name='sectionID' value=<?=$sectionID;?>>
			<input type='submit' name='addProject' value='Add'>
		</form>

		<?
		}
	
		//--------------------------------------------------
		//LIST PAGE
		//--------------------------------------------------
	
		else //sectionID is not posted (!$sectionID)
		{
			$query = "	SELECT * FROM section
						LEFT JOIN course ON section.sectionFK_courseID = course.courseID
						LEFT JOIN faculty ON sectionFK_facultyID = facultyID
						ORDER BY sectionYear DESC,
								CASE
									WHEN sectionQuarter = 'Summer' THEN 1
									WHEN sectionQuarter = 'Spring' THEN 2
									WHEN sectionQuarter = 'Winter' THEN 3
									WHEN sectionQuarter = 'Fall' THEN 4
									ELSE 5
								END;";
			$result = $database->fullQuery($query);
		?>
			
			<form action='export.php' method='post'>
				<button type='submit' name='export' value="	SELECT * FROM section
								   		LEFT JOIN course ON section.sectionFK_courseID = course.courseID
										LEFT JOIN faculty ON section.sectionFK_facultyID = faculty.facultyID
										ORDER BY sectionYear DESC, sectionQuarter;">Export All</button>
				<input type='hidden' name='outname' value='section'>
			</form>
			<table class="table table-striped" style="width:50%">
				<thead>
					<tr>
						<th>Course</th>
						<th>Name</th>
						<th>Instructor</th>
						<th>Quarter</th>
						<th>Year</th>
						<th>Status</th>
						<th>CRN</th>
						<th>Students</th>
						<th>Details</th>
					</tr>
				</thead>
				<tbody>
					<?
					while ($row = mysqli_fetch_array($result))
					{
						echo "<tr>";
						echo "<td>{$row["courseDept"]}{$row["courseNum"]}</td>";
							echo "<td>{$row["courseName"]}</td>";
						
						echo "<td>";
						if ($row["facultyName"])
						{
							echo	"<form action='faculty.php' method='post'>
										<input type='hidden' name='facultyID' value='{$row['sectionFK_facultyID']}'>
										<input type='submit' name='profile' value='{$row['facultyName']}'>
									</form>";
						}
						echo "</td>";
						echo "<td>{$row["sectionQuarter"]}</td>";
						echo "<td>{$row["sectionYear"]}</td>";
						echo "<td>{$row["sectionStatus"]}</td>";
						echo "<td>{$row["sectionCRN"]}</td>";
						echo "<td>{$row["sectionNumStudents"]}</td>";
			
						//Section Button
						echo "<form action=\"section.php\" method=\"post\"><td>";
						echo "<input type=\"hidden\" name=\"sectionID\" value=\"{$row["sectionID"]}\">";
						echo "<input type=\"submit\" name=\"profile\" value=\"Details\"></td></form>";
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
