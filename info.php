<?php
	/*
		Info
		Western Washington University
		Service Learning Center Database
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
	<title>
		Info
	</title>
	<body>
		<img border="0" src="banner.jpg" width="100%" height="150">
		<br>
		<link href="bootstrap.css" rel="stylesheet">
		<ul class="nav nav-pills">
			<li><a href="main.php">Home</a></li>
			<li class="active"><a href="info.php">Info</a></li>
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
		You must be a <font color="red">TRAINED</font> CSL staff member to use this site.<br>
		Please <font color="red">ONLY</font> use the navigation bar at the top of the page to navigate this website.<br>
		<em>Using 'back' or 'refresh' may cause the database to repeat something that was done on the previous page, such as adding a course or a comment.</em><br>
		<hr style='background:#000000; border:0; height:3px' />
		This website was created for the Center for Service Learning by three Computer Science students at Western Washington University,<br>
		Edward Gebauer, John Miller, and Toan Nguyen, from Fall 2013 to Summer 2014 for the CS491-CS493 Senior Project series.<br>
		<br>
		The website uses <a href="http://www.getbootstrap.com/">Twitter Bootstrap</a> for appearance and a <a href="http://www.mysql.com">MySQL</a> database to store the data.<br>
		For more information contact Professor <a href="mailto:James.Hearne@wwu.edu">James Hearne</a>
		or send us a message <a href="mailto:wwuslcdb@gmail.com">here.</a><br>
		<hr style='background:#000000; border:0; height:3px' />
		Western's Center for Service-Learning<br>
		360-650-7542<br>
		<a href="mailto:Service.Learning@wwu.edu">Service.Learning@wwu.edu</a><br>
		<a href="http://www.wwu.edu/csl">http://www.wwu.edu/csl</a>
    	</body>
</html>