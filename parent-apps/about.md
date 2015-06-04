# Parent Applications
The router will first look into your **APP_PATH**, but if it doesn't find a match, it will attempt to find it in your **PARENT_APP_PATH**.

This allows several applications to share large features, such as using the same /control-panel or /admin system.

### Example
If you visit the URL: yoursite.com/admin/find-users, the routing mechanism will search in this order:

1. /apps/YOUR_APP/admin/find-users
2. /parent-apps/YOUR_PARENT_APP/admin/find-users
