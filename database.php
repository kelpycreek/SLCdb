<?php
/*
	Database Functions
	Western Washington University
	Service Learning Center Database
*/

class Database extends mysqli
{
	
	//fullQuery
	//Executes a query on the database and returns the stored result set, so it can be used later
	public function fullQuery ($query)
	{
		$return = $this->multi_query($query) or die ("Error: " . mysqli_error($this) . ".<br><br>The full SQL statement executed was:<br>{$query}<br>");
		$result = $this->store_result();
		return $result;
	}
	
	//keywordSearch
	//Used to search all fields in the database for keywords
	public function keywordSearch ($string)
	{
		//Section
		$query = "SELECT * FROM section LEFT JOIN course ON course.courseID = section.sectionFK_courseID LEFT JOIN sectionComments ON section.sectionID = sectionComments.comFK_sectionID;";
		$searchFields = array("sectionCRN", "sectionQuarter", "sectionYear", "sectionCreator", "sectionCom", "courseNum", "courseName", "courseDept");
		$qry = $this->multi_query($query) or die("Error: " . mysqli_error($this) . "<br>");
		
		if($result = $this->use_result())
		{
			$printHead = False;
			while ($row = mysqli_fetch_array($result))
			{
				foreach ($searchFields as &$field)
				{
					if (strpos($row[$field], $string) !== False)
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
						
						if (strpos($row["sectionCom"], $string) !== False)
							echo '<td>' . $row["sectionCom"] . '</td>';
						else
							echo '<td></td>';

						echo '<form action="project.php" method="POST">';
						echo '<td>';
						echo '<input type="hidden" name="sectionID" value="'. $row["sectionID"] .'" method="POST">';
						echo '<input type="submit" name="profile" value="Details" method="POST">';
						echo '</td>';
						echo '</form>';
						echo '</tr>';
						break;
					}
				}
			}
			echo '</tbody>';
			echo '</table>';
		}

		//Faculty
		$query = "SELECT * FROM faculty LEFT JOIN facultyComments ON faculty.facultyID = facultyComments.comFK_facultyID;";
		$searchFields = array("facultyName", "facultyDept", "facultyDesc", "facultyCreator", "facultyCom");
		$qry = $this->multi_query($query) or die("Error: " . mysqli_error($this) . "<br>");
		
		if($result = $this->use_result())
		{
			$printHead = False;
			while ($row = mysqli_fetch_array($result))
			{
				foreach ($searchFields as &$field)
				{
					if (strpos($row[$field], $string) !== False)
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
						
						if (strpos($row["facultyDesc"], $string) !== False)
							echo '<td>' . $row["facultyDesc"] . '</td>';
						else
							echo '<td></td>';
							
						if (strpos($row["facultyCom"], $string) !== False)
							echo '<td>' . $row["facultyCom"] . '</td>';
						else
							echo '<td></td>';

						echo '<form action="project.php" method="POST">';
						echo '<td>';
						echo '<input type="hidden" name="facultyID" value="'. $row["facultyID"] .'" method="POST">';
						echo '<input type="submit" name="profile" value="Details" method="POST">';
						echo '</td>';
						echo '</form>';
						echo '</tr>';
						break;
					}
				}
			}
			echo '</tbody>';
			echo '</table>';
		}

		//Project
		$query = "SELECT * FROM project LEFT JOIN projectComments ON project.projectID = projectComments.comFK_projectID;";
		$searchFields = array("projectName", "projectType", "projectStatus", "projectDesc", "projectCreator", "projectCom", "partnerName");
		$qry = $this->multi_query($query) or die("Error: " . mysqli_error($this) . "<br>");
		
		if($result = $this->use_result())
		{
			$printHead = False;
			while ($row = mysqli_fetch_array($result))
			{
				foreach ($searchFields as &$field)
				{
					if (strpos($row[$field], $string) !== False)
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
							echo '<td>' . $row["projectCom"] . '</td>';
						else
							echo '<td></td>';

						echo '<form action="project.php" method="POST">';
						echo '<td>';
						echo '<input type="hidden" name="projectID" value="'.$row["projectID"].'" method="POST">';
						echo '<input type="submit" name="profile" value="Details" method="POST">';
						echo '</td>';
						echo '</form>';
						echo '</tr>';
						break;
					}
				}
			}
			echo '</tbody>';
			echo '</table>';
		}

		//Partner
		$query = "SELECT * FROM partner LEFT JOIN partnerComments ON partner.partnerID = partnerComments.comFK_partnerID;";
		$searchFields = array("partnerName", "partnerContact", "partnerType", "partnerDesc", "partnerCreator", "partnerCom");
		$qry = $this->multi_query($query) or die("Error: " . mysqli_error($this) . "<br>");
		
		if($result = $this->use_result())
		{
			$printHead = False;
			while ($row = mysqli_fetch_array($result))
			{
				foreach ($searchFields as &$field)
				{
					if (strpos($row[$field], $string) !== False)
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
							echo '<td>' . $row["partnerCom"] . '</td>';
						else
							echo '<td></td>';

						echo '<form action="partner.php" method="POST">';
						echo '<td>';
						echo '<input type="hidden" name="partnerID" value="'.$row["partnerID"].'" method="POST">';
						echo '<input type="submit" name="profile" value="Details" method="POST">';
						echo '</td>';
						echo '</form>';
						echo '</tr>';
						break;
					}
				}
			}
			echo '</tbody>';
			echo '</table>';
		}
	}
}

//connectSLC
//Returns a new connection to the SLC database
function connectSLC()
{
	$username = 'gebauee_writer';
	$password = 'A2AWvYYCR';
	$host = 'db.cs.wwu.edu';
	$dbname = 'db_slc1410';
	$database = new Database($host, $username, $password, $dbname)
		or die("Error connecting to the database:<br>" . mysqli_error($conn));
	return $database;
}
?>
