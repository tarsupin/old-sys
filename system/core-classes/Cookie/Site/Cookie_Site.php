<?php /*

-----------------------------------------
------ About the Cookie_Site Class ------
-----------------------------------------

This class is identical to Cookie_Server in every way but one: its cookies are exclusive to a single site (domain) on the server. It will use the FULL_DOMAIN constant to identify the site instead of BASE_DOMAIN.

If you want to remember this cookie across multiple sub-domains that are hosted on the same server, use the Cookie_Server:: class.

Note: All behavior modification is handled within Cookie_Server:: - you do not need to create any methods here.

*/

abstract class Cookie_Site extends Cookie_Server {}