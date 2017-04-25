<?php
session_start(); // needed to be able to use the $_SESSION variable
?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Disease Diagnosis System</title>

		<style>
			body {
				background: gainsboro; /* Fills the page */
				max-width: 1280px;
				margin: 0 auto;
			}
		</style>
	</head>
	<body>
		<script type="text/javascript">

			// called when a body part is selected from the combobox
			// will open the symptoms page to diagnose a disease for the selected body part
			function cbBodyPart_Changed()
			{
				// get the HTML combobox element 'cbBodyPart' as a javascript variable
				cbBodyPart = document.getElementById("cbBodyPart");

				// get the Id of the bodypart, i.e. for Shoulder the Id is 1, for Elbow the Id is 2, etc
				bodyPartId = cbBodyPart.options[cbBodyPart.selectedIndex].value;

				// get the Name of the bodypart, i.e. for Shoulder the name is 'Shoulder', etc
				bodyPartName = cbBodyPart.options[cbBodyPart.selectedIndex].text;
				
				// make sure we've got a valid body part Id
				if (bodyPartId !== "0")
				{
					// record the values Id and Name into the hiden HTML Form fields 'hdnBodyPartId' and 'hdnBodyPartName'
					document.getElementById("hdnBodyPartId").value = bodyPartId;
					document.getElementById("hdnBodyPartName").value = bodyPartName;
					
					// submit the HTML Form and its hidden fields will be processed by the next PHP page
					document.getElementById("frmBodyPart").submit();
				}
			}

			// allows the admin to enter a password and login
			function frmLogin()
			{
				// this is the input dialog where the user must enter the password
				var password = prompt("Enter the password for 'Admin':");
				if (password === null)
					return;
				else
				{
					// same as first function, record values into hidden HTML Form fields and submit the Form
					document.getElementById("hdnAction").value = "login";
					document.getElementById("hdnPassword").value = password;
					document.getElementById("frmBodyPart").action = "index.php";
					document.getElementById("frmBodyPart").submit();
				}
			}

			// logs out the admin
			function frmLogout()
			{
				// same as first function, record values into hidden HTML Form fields and submit the Form
				document.getElementById("hdnAction").value = "logout";
				document.getElementById("frmBodyPart").action = "index.php";
				document.getElementById("frmBodyPart").submit();
			}
		</script>
		
		<br/>

		<center>
			<table style="background-color:white; border: 20px solid white">
				<tr>
					<td><a href='index.php'>Home</a></td>

					<?php
					
					// if the admin tried to login and failed, give him an "unsuccessful login" message
					if (array_key_exists("hdnAction", $_POST) && $_POST["hdnAction"] == "login")
						if (array_key_exists("hdnPassword", $_POST) && $_POST["hdnPassword"] == "fuzzy")
							$_SESSION["admin"] = "fuzzy";

					// if the admin  tried to logout then reset the $_SESSION["admin"] value to "" so that he'll be logged out
					if (array_key_exists("hdnAction", $_POST) && $_POST["hdnAction"] == "logout")
						$_SESSION["admin"] = "";

					// if the admin is logged in, allow him to edit the database, otherwise the option to login
					if (array_key_exists("admin", $_SESSION) && $_SESSION["admin"] == "fuzzy")
					{
						echo "<td align='right'><a href='admin_bodyparts.php'>Edit database</a></td>";
						echo "<td align='right'><a href='index.php' onclick='frmLogout();return false;'>Logout Admin</a></td>";
					}
					else
						echo "<td align='right'></td><td align='right'><a href='index.php' onclick='frmLogin();return false;'>Login Admin</a></td>";
					?>

				</tr>
				<tr>
					<td colspan='3'><hr/></td>
				</tr>
				<tr>
					<td valign='top'>
						<center><h1>Disease diagnosis system</h1></center>
						<br/>
						<h2>Introduction</h2>
						This is a simple system for diagnosis of various diseases based on provided symptoms. The decision scoring algorithm is based on fuzzy logic.
						<br/><br/><br/>

						<!-- all fields are in an HTML form so that their values will be submitted for processing by PHP when the next page loads -->
						<form id="frmBodyPart" action="symptoms.php" method="post">
							<?php
							// if the admin tried to login and failed, give him an "unsuccessful login" message
							if (array_key_exists("hdnAction", $_POST) && $_POST["hdnAction"] == "login")
								if (array_key_exists("hdnPassword", $_POST) && $_POST["hdnPassword"] == "fuzzy")
									$_SESSION["admin"] = "fuzzy";
								else
									echo "<font color='red'><center>Unsuccessful login! Try again.</center></font>";

							// if the admin  tried to logout then reset the $_SESSION["admin"] value to "" so that he'll be logged out
							if (array_key_exists("hdnAction", $_POST) && $_POST["hdnAction"] == "logout")
								$_SESSION["admin"] = "";
							?>

							<br/>
							<br/>

							<!-- print the HTML table of all body parts -->
							<table>
								<tr><th>Make a diagnosis:</th></tr>
								<tr><td><select name="cbBodyPart" id="cbBodyPart" onChange="cbBodyPart_Changed()">
											<option value="0" selected="selected">Select a body part</option>
											<?php
											// connect to the MySQL server
											if ($connection = mysql_connect("localhost", "root", ""))
											{
												// select database 'fuzzy' as default for this connection
												if (mysql_select_db("fuzzy", $connection))
												{
													// retrieve all body parts from the MySQL table and print them as rows of an HTML table
													$records = mysql_query("SELECT * FROM body_parts ORDER BY Name", $connection);
													
													// if returned recorset is not empty
													if ($records && mysql_num_rows($records) > 0)
													{
														// process each record
														while ($row = mysql_fetch_array($records))
														{
															// extract the Id and Name of each record and make an HTML option element for it
															$bodyPartId = $row["Id"];
															$bodyPartName = $row["Name"];
															echo '<option value="' . $bodyPartId . '">' . $bodyPartName . '</option>';
														}
													}
												}
												mysql_close($connection);
											}
											?>
										</select></td></tr>
							</table>
							<br/><br/>

							<!-- here we record values with javascript so that they will be available when the next page is loaded -->
							<!-- when the next page is loaded we access them using the $_POST structure -->
							<input type='hidden' id='hdnAction' name='hdnAction' value=''>
							<input type='hidden' id='hdnPassword' name='hdnPassword' value=''>
							<input type='hidden' id='hdnBodyPartId' name='hdnBodyPartId' value=''>
							<input type='hidden' id='hdnBodyPartName' name='hdnBodyPartName' value=''>
						</form>
				
					
					</td>
					<td colspan='2'><img src='MusculoSkeletal.gif'/></td>
				</tr>
				
			</table>
			

		</center>
	</body>
</html>
