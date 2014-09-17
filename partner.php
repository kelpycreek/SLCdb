<?php
/*
	Partner Profile
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
			Partner Profile
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
			<li class="active"><a href="partner.php">Partner</a></li>
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
		$partnerName= $_POST['partnerName'];
		$partnerContact= $_POST['partnerContact'];
		$partnerDesc= $_POST['partnerDesc'];
		if ($_POST["partnerTypeText"])
			$partnerType = $_POST["partnerTypeText"];
		else
			$partnerType = $_POST["partnerType"];
			
		//Remove the profile from the database if the Delete button is clicked on the edit page
		if($delete)
		{
			$result = $database->fullQuery("DELETE FROM partner
											WHERE partnerID = '%s';",$_POST['partnerID']);
			unset($_POST['partnerID']);
		}
		
		//Get posted ID
		$partnerID = $_POST['partnerID'];

		//Update the profile when the Confirm button is clicked on the edit page
		if ($confirm)
		{
			$partnerLastMod = $date;
			if ($partnerType != "NULL")
				$partnerType = "'" . $partnerType . "'";
			
			$result = $database->fullQuery("UPDATE	partner
											SET		partnerName = '{$partnerName}',
													partnerContact = '{$partnerContact}',
													partnerType = {$partnerType},
													partnerDesc = '{$partnerDesc}',
													partnerLastMod = '{$date}',
													partnerCreator = '{$casuser}'
											WHERE	partnerID ='{$partnerID}';");
		}
		
		//Add new comments
		if ($addComment)
		{
			$newComment= $_POST["newComment"];
			$return = $database->fullQuery("INSERT INTO partnerComments
											(partnerCom, partnerComCreator, partnerComLastMod, comFK_partnerID)
											VALUES ('{$newComment}', '{$casuser}', '{$date}', {$partnerID});");
		}

		//Delete old comments
		if ($delComment)
		{
			$return = $database->fullQuery("DELETE FROM partnerComments
											WHERE partnerComID = {$delComment};");
		}
		
		?>

		<h4>Partner Profile</h4>
		
		<?
		
		//--------------------------------------------------
		//EDIT PAGE
		//--------------------------------------------------
		
		if ($edit)
		{
		?>
			<form action=partner.php method=post>
				<input type="submit" name="confirm" value="Confirm">
				<input type="submit" name="cancel" value="Cancel"></td>
				<table class="table table-striped" style="width:30%">
					<thead>
						<tr>
							<th>Name</th>
							<th>Contact</th>
							<th>Type</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<input type="text" style="width:300px" name="partnerName" size="10"
								placeholder="<?=$partnerName;?>" value="<?=$partnerName;?>">
							</td>
							<td>
								<input type="text" style="width:300px" name="partnerContact" size="10"
								placeholder="<?=$partnerContact;?>" value="<?=$partnerContact;?>">
							</td>
							<td>
								<select name="partnerType">
									<option value="NULL">[None]</option>
									<?
									$result = $database->fullQuery("SELECT DISTINCT partnerType from partner
																	ORDER BY partnerType;");
									while ($row = mysqli_fetch_array($result))
									{
										$type = $row["partnerType"];
										if ($type == $partnerType)
											echo "<option value=\"{$type}\" selected>{$type}</option>";
										else if ($type != "")
											echo "<option value=\"{$type}\">{$type}</option>";
									}
									?>
								</select>
								<input type="text" name="partnerTypeText" placeholder="Add New">
							</td>
							<input type="hidden" name="partnerID" value="<?=$partnerID;?>">
						</tr>
					</tbody>
				</table>
				<b>Description</b><br>
				<input type="text" name="partnerDesc" value="<?=$partnerDesc;?>" placeholder="<?=$partnerDesc;?>"style="width:1000px"><br>
				<br>
				<input type="submit" name="delete" value="Delete">
			</form>
		<?
		}
		
		//--------------------------------------------------
		//PROFILE PAGE
		//--------------------------------------------------
		
		else if ($partnerID)
		{
		?>
			<form action="partner.php" method="POST">
				<input type="hidden" name="partnerID" value="">
				<input type="submit" name="profile" value="View All Partners">
			</form>
			<hr style='background:#000000; border:0; height:3px' />
			<h4>Details</h4>
			Partner ID = <?=$partnerID;?>
			<table class="table table-striped" style="width:50%">
				<thead>
					<tr>
						<th>Last Modfied</th>
						<th>Modified By</th>
						<th>Name</th>
						<th>Contact</th>
						<th>Type</th>
						<th>Edit</th>
					</tr>
				</thead>
				<tbody>
				<?
				$result = $database->fullQuery("SELECT * FROM partner
												WHERE partnerID = {$partnerID};");
				$row = mysqli_fetch_array($result);
				?>
					<tr>
						<td><?=$row["partnerLastMod"]?></td>
						<td><?=$row["partnerCreator"]?></td>
						<td><?=$row["partnerName"]?></td>
						<?
						if ((!$row["partnerContact"]) || ($row["partnerContact"] == ""))
							echo '<td>[None]</td>';
						else
							echo '<td>' . $row["partnerContact"] . '</td>';
						if ($row["partnerType"])
							echo '<td>' . $row["partnerType"] . '</td>';
						else
							echo '<td>[None]</td>';
						?>
						<form action="partner.php" method="post">
							<td>
								<input type="hidden" name="partnerID" value="<?=$row["partnerID"];?>">
								<input type="hidden" name="partnerName" value="<?=$row["partnerName"];?>">
								<input type="hidden" name="partnerContact" value="<?=$row["partnerContact"];?>">
								<input type="hidden" name="partnerType" value="<?=$row["partnerType"];?>">
								<input type="hidden" name="partnerDesc" value="<?=$row["partnerDesc"];?>">
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
			$partnerDesc = $row["partnerDesc"];
			if ((!$partnerDesc) || ($partnerDesc == ""))
				echo '[None]';
			else
				echo $partnerDesc;
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
					$result = $database->fullQuery("SELECT * FROM partnerComments
													WHERE comFK_partnerID = {$partnerID};");
					while ($row = mysqli_fetch_array($result))
					{
						echo '<tr>';
						echo '<td>' . $row['partnerCom'] . '</td>';
						echo '<td>' . $row['partnerComCreator'] . '</td>';
						echo '<td>' . $row['partnerComLastMod'] . '</td>';
						echo '<form action="partner.php" method=post>';
						echo '<input type="hidden" name="partnerID" value="'. $partnerID .'">';
						echo '<input type="hidden" name="delComment" value="'. $row['partnerComID'] .'">';
						echo '<td><input type="submit" name="delCommentButton" value="Delete"></td>';
						echo '</form>';
					}
					?>
				</tbody>
			</table>
			
			<form action="partner.php" method=post>
				<td><input type="hidden" name="partnerID" value="<?=$partnerID;?>"></td>
				<td><input type="text" style="width:950px" name="newComment" placeholder="New Comment" value=""></td>
				<br>
				<td><input type="submit" name="addComment" value="Add"></td>
			</form>
			
			<hr style='background:#000000; border:0; height:3px' />
			<h4>Affiliated Projects</h4>
			<table class="table table-striped" style="width:30%">
				<thead>
					<th>Name</th>
					<th>Partner</th>
					<th>Type</th>
					<th>Status</th>
				</thead>
				<tbody>
					<?
						$result = $database->fullQuery("SELECT * FROM project
														LEFT JOIN partner ON project.projectFK_partnerID = partner.partnerID
														WHERE partnerID = {$partnerID}
														ORDER BY projectType");
						while ($row = mysqli_fetch_array($result))
						{
							echo "<tr>";
							echo "<td>" . $row["projectName"] . "</td>";
							echo "<td>" . $row["partnerName"] . "</td>";
							echo "<td>" . $row["projectType"] . "</td>";
							echo "<td>" . $row["projectStatus"] . "</td>";
							echo "<td>
									<form action='project.php' method=post>
										<input type='hidden' name='projectID' value={$row["projectID"]}>
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

		//Otherwise display the profiles
		else
		{
		?>
			<form action='export.php' method='post'>
				<button type='submit' name='export' value='SELECT * FROM partner ORDER BY partnerName;'>Export All</button>
				<input type='hidden' name='outname' value='partner'>
			</form>
			<table class="table table-striped" style="width:50%">
				<thead>
					<tr>
						<th>Name</th>
						<th>Contact</th>
						<th>Type</th>
						<th>Details</th>
					</tr>
				</thead>
				<tbody>
					<?
					$result = $database ->fullQuery("SELECT * FROM partner
													ORDER BY partnerName;");
					while ($row = mysqli_fetch_array($result))
					{
					?>
						<tr>
							<td><?=$row["partnerName"];?></td>
							<td><?=$row["partnerContact"];?></td>
							<td><?=$row["partnerType"];?></td>
							<form action="partner.php" method="post">
								<td>
									<input type="hidden" name="partnerID" value="<?=$row["partnerID"];?>">
									<input type="submit" name="profile" value="Details">
								</td>
							</form>
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