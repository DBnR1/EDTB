<?php
/**
 * Add or edit log entries
 *
 * No description
 *
 * @package EDTB\Backend
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 */

if (isset($_GET["do"])) {
    /** @require config */
    require_once($_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");
    /** @require functions */
    require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
    /** @require MySQL */
    //require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");
    /** @require MakeLog class */
    require_once("MakeLog.php");

    $data = json_decode($_REQUEST["input"]);

    $addLog = new MakeLog();
    $addLog->add_log($data);

    exit;
}
?>
<div class="input" id="addlog">
    <form method="post" id="log_form" action="/">
        <div class="input-inner">
            <div class="suggestions" id="suggestions_1" style="margin-left:8px;margin-top:130px"></div>
            <div class="suggestions" id="suggestions_41" style="margin-left:402px;margin-top:130px"></div>
            <table>
                <thead>
                    <tr>
                        <td class="heading" colspan="2">Add/Edit Log Entry
                            <span class="right">
                                <a href="javascript:void(0)" id="close_form" title="Close form">
                                    <img src="/style/img/close.png" class="icon" alt="X" />
                                </a>
                            </span>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="dark" style="text-align:left;white-space:nowrap">
                            <input type="hidden" name="edit_id" id="edit_id" />
                            <select title="Type" class="selectbox" name="log_type" id="log_type">
                                <option value="system">Type: System log</option>
                                <option value="general">Type: General log</option>
                                <option value="personal">Type: System log (Personal)</option>
                            </select>
                            <fieldset>
                                <input type="checkbox" id="pinned" name="pinned" value="1" disabled="disabled" />
                                <label for="pinned" id="label"></label>
                                <span id="pin_click" style="vertical-align:middle">
                                    &nbsp;Pin to top
                                </span>&nbsp;&nbsp;
                                <select title="Weight" class="selectbox" id="weight" name="weight" style="display:none">
                                    <?php
                                    for ($i = -30; $i < 31; $i++) {
                                        $selected = $i == 0 ? ' selected="selected"' : "";
                                        echo '<option value="' . $i . '"' . $selected . '>Weight ' . $i . '</option>';
                                    }
                                    ?>
                                </select>
                            </fieldset>
                        </td>
                        <td class="dark" style="text-align:right;width:50%">
                            <input class="textbox" type="text" name="title" id="title" placeholder="Log title (optional)" style="width:96%;margin-left:0" />
                        </td>
                    </tr>
                    <tr>
                        <td class="dark" style="text-align:left;width:50%">
                            <input class="textbox" type="text" name="system_name" placeholder="System name" id="system_1" style="width:96%;margin-left:0" oninput="showResult(this.value, '1')" />
                        </td>
                        <td class="dark" style="text-align:right;width:50%">
                            <input class="textbox" type="text" name="station_name" placeholder="Station name (optional)" id="statname" style="width:96%" oninput="showResult(this.value, '41', 'no', 'yes', 'no', $('#system_1').val())" />
                        </td>
                    </tr>
                    <tr>
                        <td class="dark" colspan="2">
                            <textarea id="html" name="log_entry" placeholder="Log entry" rows="10" cols="40"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class="dark" colspan="2" style="vertical-align:middle">
                            <span id="audio_log" class="left" style="text-align:left;margin-left:6px"></span>
                            <span class="right">
                                <a href="javascript:void(0)" title="Enable audio log" id="enable_audio">
                                    Enable audio
                                </a>
                                <a href="javascript:void(0)" title="Start recording audio" id="record_click" style="display:none">
                                    <img class="icon24" src="/style/img/record.png" alt="Rec" id="record" style="margin-top:10px;margin-bottom:10px" />
                                </a>
                                <a href="javascript:void(0)" title="Stop recording audio" id="stop_click" style="display:none">
                                    <img class="icon24" src="/style/img/stop.png" alt="Stop" id="stop" style="margin-top:10px;margin-bottom:10px" />
                                </a>
                            </span>
                            <ul id="recordingslist"></ul>
                            <input id="audiofiles" type="hidden" name="audiofiles" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td class="dark" colspan="2">
                            <a href="javascript:void(0)">
                                <div class="button" id="submit_log">Submit log entry</div>
                            </a>
                            <span id="delete"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
</div>
<script>
    var enable_audio = $("#enable_audio"),
        record_click = $("#record_click"),
        stop_click = $("#stop_click"),
        pin_click = $("#pin_click"),
        pinned = $("#pinned"),
        weight = $("#weight");

    enable_audio.click(function()
    {
        enable_audio.hide();
        record_click.show();
        start_audio();
    });

    record_click.click(function()
    {
        startRecording(this);
        record_click.hide();
        stop_click.show();
        //$("#stop").attr("src", "/style/img/stop.png");
    });

    stop_click.click(function()
    {
        stopRecording(this);
        stop_click.hide();
    });

    pin_click.click(function()
    {
        if (pinned.is(":checked"))
        {
            pinned.prop("checked", false);
            pin_click.html("&nbsp;Pin to top");
        }
        else
        {
            pinned.prop("checked", true);
            pin_click.html("&nbsp;Pinned to top");
        }
        weight.toggle();
        weight.val("0");
    });

    $("#close_form").click(function()
    {
        tofront("addlog");
        $(".addstations").toggle();
        $("#log_form")[0].reset();
    });

    $("#submit_log").click(function()
    {
        update_data("log_form", "/Log/add_log.php?do", true);
        tofront("null", true);
        $("#log_form")[0].reset();
        $("#recordingslist").html("");
        return false
    });
</script>
<script>
    var log_type = $("#log_type");

    log_type.change(function()
    {
        log_type.find("option:selected").each(function()
        {
            var value = $(this).val(),
                system_1 = $("#system_1"),
                statname = $("#statname");

            if (value == "general")
            {
                system_1.val("");
                system_1.hide();
                statname.val("");
                statname.hide();
            }
            else if (value == "system")
            {
                statname.show();
                system_1.show();
                get_cs("system_1");
                system_1.attr("placeholder", "System name");
            }
            else if (value == "personal")
            {
                statname.show();
                system_1.show();
                get_cs("system_1");
            }
        });
    }).change();
</script>
