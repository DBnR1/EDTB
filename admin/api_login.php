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
 * Login to FD API
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

$pagetitle = "Companion API login";

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

/*
*	send login details
*/

if (isset($_GET["login"]) && isset($_POST["email"]) && isset($_POST["password"]))
{
	$email = $_POST["email"];
	$password = $_POST["password"];

	if (!empty($email) && !empty($password))
	{
		exec("\"" . $curl_exe . "\" -c \"" . $cookie_file . "\" -H \"User-Agent: " . $agent . "\" -d email=" . $email . " -d password=\"" . urlencode($password) . "\" \"https://companion.orerve.net/user/login\" -k", $out);
	}

	if (!empty($out))
	{
		$error = json_encode($out);
		write_log("Error: " . $error . "", __FILE__, __LINE__);
	}
}

/*
*	send verification code
*/

if (isset($_GET["sendcode"]))
{
	$code = $_POST["code"];

	if (!empty($code))
	{
		exec("\"" . $curl_exe . "\" -b \"" . $cookie_file . "\" -c \"" . $cookie_file . "\" -H \"User-Agent: " . $agent . "\" -d code=" . $code . " \"https://companion.orerve.net/user/confirm\" -k", $out);
	}

	if (!empty($out))
	{
		$error = json_encode($out);
		write_log("Error: " . $error . "", __FILE__, __LINE__);
	}
}
?>
<div class="entries">
	<div class="entries_inner">
		<?php
		if (isset($_GET["login"]) && !isset($_GET["sendcode"]))
		{
			/*
			*	check if we need the code
			*/

			exec("". $curl_exe . " -b " . $cookie_file . " -c " . $cookie_file . " -H \"User-Agent: " . $agent . "\" \"https://companion.orerve.net/profile\" -k", $out);

			if (!empty($out))
			{
				echo notice('Something went awry. Possibly a wrong email or password?<br />Try again.', "API error");
			}
			else
			{
			?>
			<div class="input" style="display:block">
				<form method="post" action="/admin/api_login.php?sendcode">
					<div class="input-inner">
						<table>
							<tr>
								<td class="heading">Companion API Verification Code</td>
							</tr>
							<tr>
								<td class="dark">
									<span class="left"><img src="/style/img/about.png" alt="Info" style="margin-right:5px" /></span>You should now have received a verification code to your email.<br />Copy and paste it here, then click Send.
								</td>
							</tr>
							<tr>
								<td class="dark">
									<input class="textbox" type="text" name="code" placeholder="Verification Code" style="width:410px" required autofocus />
								</td>
							</tr>
							<tr>
								<td class="dark">
									<button type="submit" class="button">Send</button>
								</td>
							</tr>
						</table>
					</div>
				</form>
			</div>
			<?php
			}
		}
		elseif (isset($_GET["sendcode"]) && isset($_POST["code"]))
		{
			echo notice('The companion api is now connected.<br />Click the refresh icon to initialize.<br /><a id="api_refresh" href="javascript:void(0)" onclick="refresh_api()" title="Refresh API data"><img src="/style/img/refresh_24.png" alt="Refresh" style="height:24px;width:24px" /></a>', "API connected");
		}
		else
		{
			/*
			*	check if cookies are good (when are they not?)
			*/

			exec("\"". $curl_exe . "\" -b \"" . $cookie_file . "\" -c \"" . $cookie_file . "\" -H \"User-Agent: " . $agent . "\" \"https://companion.orerve.net/profile\" -k", $out);

			if (!empty($out))
			{
				echo notice('The companion api is already connected.<br />Click the refresh icon to refresh.<br /><a id="api_refresh" href="javascript:void(0)" onclick="refresh_api()" title="Refresh API data"><img src="/style/img/refresh_24.png" alt="Refresh" style="height:24px;width:24px" /></a>', "API already connected");
			}
			else
			{
				?>
				<div class="input" style="display:block">
					<form method="post" action="/admin/api_login.php?login">
						<div class="input-inner">
							<table style="width:340px">
								<tr>
									<td class="heading">Companion API Login</td>
								</tr>
								<tr>
									<td class="dark">
										<span class="left"><img src="/style/img/about.png" alt="Info" style="margin-right:5px" /></span>Provide the email address and password you use to login to <strong>Elite Dangerous</strong>.
									</td>
								</tr>
								<tr>
									<td class="dark">
										<input class="textbox" type="text" name="email" placeholder="ED account email address" style="width:330px" required autofocus />
									</td>
								</tr>
								<tr>
									<td class="dark">
										<input class="textbox" type="password" name="password" placeholder="ED account password" style="width:330px" required />
									</td>
								</tr>
								<tr>
									<td class="dark">
										<button type="submit" class="button">Log in</button>
									</td>
								</tr>
							</table>
						</div>
					</form>
				</div>
				<?php
			}
		}
		?>
	</div>
</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
