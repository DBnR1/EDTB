ED ToolBox
==========

INSTALLATION:
-------------

- ED ToolBox requires the 32bit version of Visual C++ Redistributable Package 2015 (vc_redist.x86.exe):
  https://www.microsoft.com/en-us/download/details.aspx?id=48145

  Without it, you'll get a missing dll error during install.

  Start EDTBManager_x_x_x.exe and follow the instructions in the setup wizard.

- After install is successfull, start ED ToolBox. A tray icon will appear. If the tray icon turns blue,
  that means the services are running and everything is fine. Right click the tray icon and choose
  "Open ED ToolBox". This will open the app in your web browser. An install prompt will appear that
  will quide you trough the rest of the process.


USAGE:
-----

How do I start using it?

- VerboseLogging needs to be on for the app to know your in-game location.
  Instructions on how to enable VerboseLogging: http://edtb.xyz/?q=download

I've already had VerboseLogging on for a while. Can I import that data to EDTB?

- Yes. Once you've installed EDTB, open http://localhost:3001/admin/import.php in your web browser
  to import old data.

How do I access the VoiceAttack module?

- In VoiceAttack, set the following urls as variables:
	- Information about current system: http://localhost:3001/Marvin/SystemData.php?sys
	- Nearest station: http://localhost:3001/Marvin/SystemData.php?cs
	- Random quote/thought: http://localhost:3001/Marvin/SystemData.php?rm
	- Check for new GalNet articles: http://localhost:3001/Marvin/GalnetData.php
	- Four latest GalNet articles are in:
		- http://localhost:3001/Marvin/galnet1.txt
		- http://localhost:3001/Marvin/galnet2.txt
		- http://localhost:3001/Marvin/galnet3.txt
		- http://localhost:3001/Marvin/galnet4.txt


FOR MORE INFORMATION VISIT HTTP://EDTB.XYZ
------------------------------------------