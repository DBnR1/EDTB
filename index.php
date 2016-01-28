<?php
/*
*  ED ToolBox, a companion web app for the video game Elite Dangerous
*  (C) 1984 - 2016 Frontier Developments Plc.
*  ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*  This program is free software; you can redistribute it and/or
*  modify it under the terms of the GNU General Public License
*  as published by the Free Software Foundation; either version 2
*  of the License, or (at your option) any later version.
*
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program; if not, write to the Free Software
*  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

/**
 * Front page
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
*/

/*
*	if the user is new, show an installation page
*/

if (file_exists("" . $_SERVER["DOCUMENT_ROOT"] . "/install.php"))
{
	$inst_path = str_replace("EDTB", "", $_SERVER["DOCUMENT_ROOT"]);
	require_once("" . $inst_path . "/data/server_config.inc.php");

	/*
	*
	*/

	require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/installer_style.php");
	require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");

	/*
	*	if mysql insertion was succesfull, remove install files
	*/

	if (isset($_GET["doned"]))
	{
		$script_path = "" . $settings["install_path"] . "\\EDTB\\install.sql";
		$installer_path = "" . $settings["install_path"] . "\\EDTB\\install.php";

		if (file_exists($script_path))
		{
			unlink($script_path);
		}
		if (file_exists($installer_path))
		{
			unlink($installer_path);
		}

		header('Location: /index.php?dones');
		exit;
	}

	echo installer_header();

	/*
	*	if mysql insertion was succesfull, ask to finish setup
	*/

	if (isset($_GET["done"]))
	{
		echo notice("<strong>Nice!</strong><br />Now all you need to do is right click the ED ToolBox icon on your system tray and select \"Update system and station data\" to fetch the latest system and station data.<br />The update takes anything from a few seconds to a few minutes depending on your system.<br /><br />After you've done that, <a href='/index.php?doned'>click here to remove the installation files and finish the setup</a>.", "Install ED ToolBox 2/2");
		echo installer_footer();
		exit;
	}

	/*
	*	if install is done but install.php still exists, something's gone wrong
	*/

	if (isset($_GET["dones"]))
	{
		echo notice("Removal of installation files was unsuccesfull.<br />Please delete install.php and install.sql from the EDTB folder.", "Install ED ToolBox");
		echo installer_footer();
		exit;
	}

	if (!isset($pwd))
	{
		echo notice("Looks like this is your first time running ED Toolbox.<br />Congratulations on your excellent choice!<br /><br />To get you set up, you need to run a script that creates the necessary database tables. The script may take a while to run.<br /><br /><div id='text' style='text-align:center'><a href='install.php?install' onclick='document.getElementById(\"loadin\").style.display=\"block\";document.getElementById(\"text\").style.display=\"none\"'>Click here when you're ready to go.</a></div><div id='loadin' style='text-align:center;display:none'><img src='/style/img/loading.gif' alt='Loading' \\></div>", "Install ED ToolBox 1/2");
	}
	else
	{
		echo notice("Looks like you've recently updated ED ToolBox to a new version.<br /><br />To get everything set up, you need to run a script that makes any necessary changes to the database. The script may take a while to run.<br /><br /><div id='text' style='text-align:center'><a href='install.php?upgrade' onclick='document.getElementById(\"loadin\").style.display=\"block\";document.getElementById(\"text\").style.display=\"none\"'>Click here when you're ready to go.</a></div><div id='loadin' style='text-align:center;display:none'><img src='/style/img/loading.gif' alt='Loading' \\></div>", "Upgrade ED ToolBox 1/2");
	}

	echo installer_footer();
	exit;
}

$pagetitle = "ED ToolBox";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

if (isset($_GET["import_done"]))
{
	echo '<div class="entries"><div class="entries_inner">';
	?>
	<script type="text/javascript">
		$.ajax(
		{
			url: "/get/getMapPoints.json.php",
			cache: false,
			dataType: 'html',
			success: function()
			{
				//console.log('success')
			}
		});
	</script>
	<?php
	echo notice("Succesfully added " . number_format($_GET["num"]) . " visited systems to the database.", "Logs imported");
	echo '</div></div>';
	require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
	exit;
}
?>
<div class="entries">
	<div class="entries_inner" id="scrollable">
	</div>
</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
