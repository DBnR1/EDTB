<?php
/**
 * DB Migration class
 *
 * No description
 *
 * @package EDTB\Install
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

 /*
 * ED ToolBox, a companion web app for the video game Elite Dangerous
 * (C) 1984 - 2016 Frontier Developments Plc.
 * ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/style/installer_style.php");

/**
 * Class used to migrate database changes between versions
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 */
class db_create
{
	/** @var resource MySQL link */
    private $link;

	/**
	 * Start DB connection
	 *
	 * @author Mauri Kujala <contact@edtb.xyz>
	 */
    public function __construct()
	{
		$ini_dir = str_replace("/EDTB", "", $_SERVER['DOCUMENT_ROOT']);
		require_once($ini_dir . "/data/server_config.inc.php");
		$host = $server;
		$username = $user;
		$password = $pwd;

        $this->link = new mysqli($host, $username, $password);

        if ($this->link->connect_error)
		{
            die("Connection failed: " . $this->link->connect_error);
        }
    }

	/**
	 * Create database
	 *
	 * @param string $db
	 * @return string|null
	 * @author Mauri Kujala <contact@edtb.xyz>
	 */
    function db($db)
	{
		write_log("Running query: CREATE DATABASE IF NOT EXISTS `" . $db . "` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci", $file, $line);
		$this->link->query("CREATE DATABASE IF NOT EXISTS `" . $db . "` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci") or write_log(mysqli_error($this->link), __FILE__, __LINE__);
	}

	/**
	 * Create table
	 *
	 * @param string $table
	 * @param string $sql
	 * @param string $modify
	 * @param string $file
	 * @param string $line
	 * @param string $database
	 * @return string|null
	 * @author Mauri Kujala <contact@edtb.xyz>
	 */
    function table($table, $sql, $modify, $file, $line, $database = "elite_log")
	{
		/**
		 * check if table exists
		 */

		$query = $this->link->query("	SELECT COLUMN_NAME FROM
										information_schema.COLUMNS
										WHERE TABLE_SCHEMA = '" . $database . "'
										AND TABLE_NAME = '" . $table . "'") or write_log("Table " . $table . " doesn't exist, attempting to create it", $file, $line);

		$num = mysqli_num_rows($query);

		$columns = explode(",>>", $sql);
		$modifies = explode(";", $modify);

		if ($num > 0)
		{
			$all_columns = "";
			foreach ($columns as $column)
			{
				preg_match_all("/\`(.*?)\`/", $column, $matches);
				$column_name = $matches[1][0];
				$all_columns[] = $column_name;
				$column_sql = $column;

				/**
				 * check if column exists
				 */

				$column_query = $this->link->query("	SELECT COLUMN_NAME FROM
														information_schema.COLUMNS
														WHERE TABLE_SCHEMA = '" . $database . "'
														AND TABLE_NAME = '" . $table . "'
														AND COLUMN_NAME = '" . $column_name . "'
														LIMIT 1") or write_log(mysqli_error($this->link), $file, $line);

				$num_column = mysqli_num_rows($column_query);

				if ($num_column > 0)
				{
					write_log("Running query: ALTER TABLE " . $database . ".`" . $table . "` CHANGE `" . $column_name . "` " . $column_sql . "", $file, $line);
					$this->link->query("ALTER TABLE " . $database . ".`" . $table . "` CHANGE `" . $column_name . "` " . $column_sql . "")
					or write_log(mysqli_error($this->link), $file, $line);

					$info = mysqli_info($this->link);
					write_log($info, $file, $line);
				}
				else
				{
					write_log("Running query: ALTER TABLE " . $database . ".`" . $table . "` ADD " . $column_sql . " AFTER `" . $prev_column . "`", $file, $line);
					$this->link->query("ALTER TABLE " . $database . ".`" . $table . "` ADD " . $column_sql . " AFTER `" . $prev_column . "`")
					or write_log(mysqli_error($this->link), $file, $line);

					$info = mysqli_info($this->link);
					write_log($info, $file, $line);
				}

				$prev_column = $column_name;
			}

			/**
			 * remove any superfluous columns
			 */

			while ($arr = mysqli_fetch_assoc($query))
			{
				if (!in_array($arr["COLUMN_NAME"], $all_columns))
				{
					write_log("Running query: ALTER TABLE " . $database . ".`" . $table . "` DROP COLUMN `" . $arr["COLUMN_NAME"] . "`", $file, $line);
					$this->link->query("ALTER TABLE " . $database . ".`" . $table . "` DROP COLUMN `" . $arr["COLUMN_NAME"] . "`")
					or write_log(mysqli_error($this->link), $file, $line);

					$info = mysqli_info($this->link);
					write_log($info, $file, $line);
				}
			}

			foreach ($modifies as $mod)
			{
				if ($mod != "")
				{
					write_log("Running query: ALTER TABLE " . $database . ".`" . $table . "` " . $mod . "", $file, $line);
					$this->link->query("ALTER TABLE " . $database . ".`" . $table . "` " . $mod . "") or write_log(mysqli_error($this->link), $file, $line);

					$info = mysqli_info($this->link);
					write_log($info, $file, $line);
				}
			}
		}
		else
		{
			write_log("Running query: CREATE TABLE IF NOT EXISTS " . $database . ".`" . $table . "` (" . str_replace(">>", "", $sql) . ") ENGINE=InnoDB DEFAULT CHARSET=latin1", $file, $line);
			$this->link->query("CREATE TABLE IF NOT EXISTS " . $database . ".`" . $table . "` (" . str_replace(">>", "", $sql) . ") ENGINE=InnoDB DEFAULT CHARSET=latin1") or write_log(mysqli_error($this->link), $file, $line);

			$info = mysqli_info($this->link);
			write_log($info, $file, $line);

			foreach ($modifies as $mod)
			{
				if ($mod != "")
				{
					write_log("Running query: ALTER TABLE " . $database . ".`" . $table . "` " . $mod . "", $file, $line);
					$this->link->query("ALTER TABLE " . $database . ".`" . $table . "` " . $mod . "") or write_log(mysqli_error($this->link), $file, $line);

					$info = mysqli_info($this->link);
					write_log($info, $file, $line);
				}
			}
        }
    }

	/**
	 * Run a SQL query
	 *
	 * @param string $sql
	 * @param string $file
	 * @param string $line
	 * @author Mauri Kujala <contact@edtb.xyz>
	 */
	function run_sql($sql, $file, $line)
	{
		write_log("Running query: " . $sql, $file, $line);
		$this->link->query($sql) or write_log(mysqli_error($this->link), $file, $line);

		$info = mysqli_info($this->link);
		write_log($info, $file, $line);
	}

	/**
	 * Close DB connection
	 *
	 * @author Mauri Kujala <contact@edtb.xyz>
	 */
    public function __destruct()
	{
        $this->link->close();
    }
}
