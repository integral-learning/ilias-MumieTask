testserver:
	url:		 https://test.mumie.net/iliasdocker/ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSelectedItems
	admin:		 root
	password:	 4dm1n11sT?At0r (oder, wenn der nicht funktioniert, "supersicher27")


Execute tests:

	run the following command in /var/www/html

	./phpunit Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/test/ilMumieTaskSuite.php

	
