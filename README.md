ED ToolBox
==========

ED ToolBox is a companion web app for [Elite Dangerous] that runs on the user's computer, allowing a virtually real-time updating of location based data.

For instructions and downloads visit [edtb.xyz].

Key features
------------

- Real time system and station data based on your in-game location (data downloaded to the user's system from ED ToolBox server, which in turn gets its data from [EDDB])
- General & System specific captains log
- Two maps: Galaxy Map and a dynamically updating "Neighborhood Map", both showing your visited systems, points of interest and bookmarked systems + some other stuff.
- Add Points of Interest
- Bookmark systems
- Find nearest system/station by allegiance, power, or what modules or ships they are selling
- Read latest GalNet news
- Screenshot gallery: screenshots automatically converted to jpg and categorized by the system they were taken in. Option to upload to imgur straight from ED ToolBox
- VoiceAttack module: Meet "Marvin", the foul mouthed ship computer; get information about current system, closest station, latest GalNet articles + more with voice commands. Marvin really hates the Federation, so don't have any little kids or stuck up adults around when you're in Federation space.
- A notepad for taking some quick notes (mission directives, kill orders, etc.)
- Show currently playing song from Foobar2000 or any player that can store the current song in a text file, or from VLC Media Player using the web interface.

Requirements
------------

- [Visual C++ Redistributable for Visual Studio 2015] - **32 bit version**

- Latest version of **Google Chrome** browser recommended for optimal experience. Latest versions of Mozilla Firefox and Microsoft Edge also work but to a limited degree.
- VerboseLogging needs to be on. To do this, locate your AppConfig.xml file.
	- In **Elite Dangerous: Horizons** the file is located in the ```elite-dangerous-64``` folder, which is located in one of the following folders, depending on your install:
		- C:\Users\%USERNAME%\AppData\Local\Frontier_Developments\Products
		- C:\Program Files (x86)\Frontier\EDLaunch\Products
		- C:\Program Files (x86)\Steam\steamapps\common\Elite Dangerous Horizons\Products
		- C:\Program Files (x86)\Frontier\Products
	- In **Elite Dangerous 1.5** it's located in the folder named ```FORC-FDEV-D-XX``` which will be located in one of the above locations, once again depending on your install.
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


[Visual C++ Redistributable for Visual Studio 2015]: <https://www.microsoft.com/en-us/download/details.aspx?id=48145>
[EDDB]: <http://eddb.io>
[VoiceAttack]: <http://www.voiceattack.com/>
[Elite Dangerous]: <http://www.elitedangerous.com>
[edtb.xyz]: <http://edtb.xyz>