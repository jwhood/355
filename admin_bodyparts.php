<?php
session_start(); // needed to be able to use the $_SESSION variable

if (!array_key_exists("admin", $_SESSION) || $_SESSION["admin"] != "fuzzy")
	header("Location: index.php");
?>

<!DOCTYPE html>

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Add/Edit/Delete Body Parts</title>

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

			// insert a new body part
			function frmBodyPart_Insert()
			{
				// the user is prompted to enter a new body part
				var bodyPartNewName = prompt("Enter a name for the new body part:", "");
				
				// ensure that the user didn't leave the dialog blank or clicked Cancel
				if (bodyPartNewName !== null && bodyPartNewName !== "")
				{
					// save the user's input in the hidden HTML Form fields and submit
					document.getElementById("hdnAction").value = "insert";
					document.getElementById("hdnBodyPartNewName").value = bodyPartNewName;
					document.getElementById("frmBodyPart").submit();
				}
			}

			// edit the name of an already existing body part
			function frmBodyPart_Edit(bodyPartId, bodyPartName)
			{
				// same pattern (and comments) as frmBodyPart_Insert() above
				var bodyPartNewName = prompt("Rename the body part", bodyPartName);
				if (bodyPartNewName !== null && bodyPartNewName !== "")
				{
					document.getElementById("hdnAction").value = "edit";
					document.getElementById("hdnBodyPartId").value = bodyPartId;
					document.getElementById("hdnBodyPartName").value = bodyPartName;
					document.getElementById("hdnBodyPartNewName").value = bodyPartNewName;
					document.getElementById("frmBodyPart").submit();
				}
			}

			// delete an already existing body part
			function frmBodyPart_Delete(bodyPartId, bodyPartName)
			{
				// same pattern (and comments) as frmBodyPart_Insert() above
				if (confirm("Are you sure you want the body part '" + bodyPartName + "' deleted? All symptoms, diseases and rules associated to this body part will be deleted as well."))
				{
					document.getElementById("hdnAction").value = "delete";
					document.getElementById("hdnBodyPartId").value = bodyPartId;
					document.getElementById("hdnBodyPartName").value = bodyPartName;
					document.getElementById("frmBodyPart").submit();
				}
			}

			// open a the symptoms php page for the selected body part
			function frmBodyPart_Symptoms(bodyPartId, bodyPartName)
			{
				// same pattern as above, save values in hidden HTML Form fields and submit
				document.getElementById("hdnAction").value = "symptoms";
				document.getElementById("hdnBodyPartId").value = bodyPartId;
				document.getElementById("hdnBodyPartName").value = bodyPartName;
				document.getElementById("frmBodyPart").action = "admin_symptoms.php";
				document.getElementById("frmBodyPart").submit();
			}

			// open a the diseases php page for the selected body part
			function frmBodyPart_Diseases(bodyPartId, bodyPartName)
			{
				// same pattern as above, save values in hidden HTML Form fields and submit
				document.getElementById("hdnAction").value = "diseases";
				document.getElementById("hdnBodyPartId").value = bodyPartId;
				document.getElementById("hdnBodyPartName").value = bodyPartName;
				document.getElementById("frmBodyPart").action = "admin_diseases.php";
				document.getElementById("frmBodyPart").submit();
			}
		</script>

		<center>
		
			<br/>
			<table style="background-color:white; border: 20px solid white">
				<tr>
					<td><a href='index.php'>Home</a></td>
					<td align='right'></td>
					<td align='right'></td>
				</tr>
				<tr>
					<td colspan='3'><hr/></td>
				</tr>
				<tr>
					<td valign='top'>
						<center><h1>Disease diagnosis system</h1></center>
						<br/>
						<h2>Body Parts</h2>
						Select a body part to edit.
						<br/><br/><br/>

						<?php
						// if the previous php page made a change to the body parts, now read it from the $_POST structure and do its appropriate action (insert/edit/delete)
						if (array_key_exists("hdnAction", $_POST))
						{
							// retrieve values submitted by the previous php page
							$bodyPartId = $_POST["hdnBodyPartId"];
							$bodyPartName = $_POST["hdnBodyPartName"];
							$bodyPartNewName = $_POST["hdnBodyPartNewName"];

							// insert the new body part to the database
							if ($_POST["hdnAction"] == "insert")
							{
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									if (mysql_select_db("fuzzy", $connection))
									{
										$query = sprintf("SELECT * FROM body_parts WHERE Name='%s'", $bodyPartNewName);
										$records = mysql_query($query, $connection);

										if (mysql_num_rows($records) > 0)
											echo sprintf("Error! Body part '%s' already exists in the database. No new record was inserted.<br>", $bodyPartNewName);
										else
										{
											$query = sprintf("INSERT INTO body_parts (Name) VALUES ('%s')", $bodyPartNewName);
											$retval = mysql_query($query, $connection);
											if ($retval)
												echo "<font color='green'>New body part '" . $bodyPartNewName . "' inserted into the database</font><br>";
											else
												echo "<font color='red'>Could not insert new body part '" . $bodyPartNewName . "': " . mysql_error() . "</font>";
										}
									}
									mysql_close($connection);
								}
							}
							// edit an existing body part in the database
							else if ($_POST["hdnAction"] == "edit")
							{
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									if (mysql_select_db("fuzzy", $connection))
									{
										$query = sprintf("SELECT * FROM body_parts WHERE Name='%s'", $bodyPartNewName);
										$records = mysql_query($query, $connection);

										if (mysql_num_rows($records) > 0)
											echo sprintf("<font color='red'>Error! Body part '%s' already exists in the database. '%s' was not renamed to '%s'.</font><br>", $bodyPartNewName, $bodyPartName, $bodyPartNewName);
										else
										{
											$query = sprintf("UPDATE body_parts SET Name='%s' WHERE Id=%s", $bodyPartNewName, $bodyPartId);
											$retval = mysql_query($query, $connection);
											if ($retval)
												echo "<font color='green'>Body part '" . $bodyPartName . "' renamed to '" . $bodyPartNewName . "'</font><br>";
											else
												echo "<font color='red'>Could not rename body part '" . $bodyPartName . "': " . mysql_error() . "</font>";
										}
									}
									mysql_close($connection);
								}
							}
							// delete an existing body part in the database
							elseif ($_POST["hdnAction"] == "delete")
							{
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									if (mysql_select_db("fuzzy", $connection))
									{
										$query = sprintf("DELETE FROM body_parts WHERE Id=%s", $bodyPartId);
										$retval = mysql_query($query, $connection);
										if ($retval)
											echo "<font color='green'>Body part '" . $bodyPartName . "' deleted from database</font><br>";
										else
											echo "<font color='red'>Could not delete body part '" . $bodyPartName . "': " . mysql_error() . "</font>";
									}
									mysql_close($connection);
								}
							}
							$_POST["hdnAction"] = "";
						}
						?>

						<!-- all fields are in an HTML form so that their values will be submitted for processing by PHP when the next page loads -->
						<form id="frmBodyPart" action="admin_bodyparts.php" method="post"> 
							<table border="1">
								<th>Body Part Name</th>  
								<th>Edit</th>
								<th>Delete</th>
								<th>Symptoms</th>
								<th>Diseases</th>
								<?php
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									if (mysql_select_db("fuzzy", $connection))
									{
										$records = mysql_query("SELECT * FROM body_parts ORDER BY Name", $connection);
										if ($records && mysql_num_rows($records) > 0)
										{
											// print each body part as a row in the HTML table
											while ($row = mysql_fetch_array($records))
											{
												$bodyPartId = $row["Id"];
												$bodyPartName = $row["Name"];
												echo "<tr>";
												echo "<td>" . $bodyPartName . "</td>";
												echo "<td><button type='button' onclick='frmBodyPart_Edit(" . $bodyPartId . ", \"" . $bodyPartName . "\")'>Edit</button></td>";
												echo "<td><button type='button' onclick='frmBodyPart_Delete(" . $bodyPartId . ", \"" . $bodyPartName . "\")'>Delete</button></td>";
												echo "<td><button type='button' onclick='frmBodyPart_Symptoms(" . $bodyPartId . ", \"" . $bodyPartName . "\")'>Symptoms</button></td>";
												echo "<td><button type='button' onclick='frmBodyPart_Diseases(" . $bodyPartId . ", \"" . $bodyPartName . "\")'>Diseases</button></td>";
												echo "</tr>";
											}
										}
									}
									mysql_close($connection);
								}
								?>
								<tr><td colspan='5'><center><button type='button' onclick='frmBodyPart_Insert()'>Insert New Body Part</button></center></td></tr>
							</table>
							<br/><br/>
							<!-- here we record values with javascript so that they will be available when the next page is loaded -->
							<!-- when the next page is loaded we access them using the $_POST structure -->
							<input type='hidden' id='hdnAction' name='hdnAction' value=''>
							<input type='hidden' id='hdnBodyPartId' name='hdnBodyPartId' value=''>
							<input type='hidden' id='hdnBodyPartName' name='hdnBodyPartName' value=''>
							<input type='hidden' id='hdnBodyPartNewName' name='hdnBodyPartNewName' value=''>
						</form>

					</td>
					<td colspan='2'><img src='MusculoSkeletal.gif'/></td>
				</tr>
				
			</table>
			
		</center>
	</body>
</html>
