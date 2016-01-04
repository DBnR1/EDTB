ED ToolBox
==========

ED ToolBox is a companion web app for [Elite Dangerous] that runs on the user's computer, allowing a virtually real-time updating of location based data.

Key features
------------

- Real time system and station data based on your in-game location (data downloaded to the user's system from EDTB server, which in turn gets its data from EDDB)
- General & System specifig captains log
- Two maps: galaxy map and a dynamically updating "Neighborhood Map", both showing your visited systems, points of interest and bookmarked systems + some other stuff.
- Add Points of Interest
- Bookmark systems
- Find nearest system/station by allegiance, power, or what modules or ships they are selling
- Read latest GalNet news
- Screenshot gallery: screenshots automatically converted to jpg and categorized by the system they were taken in. Option to upload to imgur straight from EDTB
- VoiceAttack module: Meet "Marvin", the foul mouthed ship computer; get information about current system, closest station or latest GalNet articles with voice commands. Marvin really hates the Federation, so don't have any little kids or stuck up adults around when you're in Federation space.
- A notepad for taking some quick notes
- Show currently playing song from Foobar2000 or any player that can store the current song in a text file

Requirements
------------

- [Visual C++ Redistributable for Visual Studio 2015] - **32 bit version**

- The automated data update script requires PowerShell 2.0 or higher, included by default since Windows 7, otherwise download from microsoft.com
- Latest version of Google Chrome browser recommended for optimal experience. Latest versions of Mozilla Firefox and Internet Explorer also work but to a limited degree.
- VerboseLogging needs to be on. To do this, locate your AppConfig.xml file.
  On Horizons the file is located in:
  * **C:\Users\%USERNAME%\AppData\Local\Frontier_Developments\Products\elite-dangerous-64\Logs**
  * On E:D 1.5 it's located in:
  * **C:\Users\%USERNAME%\AppData\Local\Frontier_Developments\Products\FORC-FDEV-D-XX**
  * Open the file in a text editor and scroll to the bottom. Replace this part:
   
  
    ```
    	<Network
    	  Port="0"
          upnpenabled="1"
    	  LogFile="netLog"
    	  DatestampLog="1"
    	  >
    ```
    * with this:
    ```
    	  <Network
    	  Port="0"
          upnpenabled="1"
    	  LogFile="netLog"
    	  DatestampLog="1"
    	  VerboseLogging="1"
    	  >
    ```
    * and save the file.
- VoiceAttack feature requires [VoiceAttack]

How do I start using it?
------------------------
- After install is successfull, start ED ToolBox. A tray icon will appear. If the tray icon turns blue, that means the services are running and everything is fine. Right click the tray icon and choose "Open ED ToolBox". This will open the app in your web browser. An install prompt will appear that will quide you trough the rest of the process.

I've already had VerboseLogging on for a while. Can I import that data to EDTB?
------------------------------------------------------------------------------------
- Yes. Once you've installed EDTB, open http://localhost:3001/admin/import.php in your web browser to import old data.

What exactly happens when I install EDTB?
------------------------------------------------
- The EDTB installer sets up a basic web server (Apache, MySQL and PHP) on the user's computer and installs a simple manager to easily control the server. EDTB will be accessed trough the user's web browser. The web server is only accessible from the local network for security reasons.

- The best way to enjoy EDTB is to run Elite Dangerous in borderless window mode and have a second monitor for EDTB so you can see information at a glance and interact with EDTB easily. Run EDTB in full screen mode (press F11) for maximum coolness. You can even access EDTB from a secondary device such as your smart phone or tablet as long as it's in the same local network.

[Visual C++ Redistributable for Visual Studio 2015]: <https://www.microsoft.com/en-us/download/details.aspx?id=48145>
[edtb]: <https://github.com/joemccann/dillinger.git>
[VoiceAttack]: <http://www.voiceattack.com/>
[Elite Dangerous]: <http://www.elitedangerous.com>
