# Initial Setup

### Step #1 - Make sure your Server Configurations are set

1. Check if you have a /config directory.
   * If you don't, copy the contents in "/system/setup/config" to "/config"

### Step #2 - Set up your Site
This example will describe how to create the site "phpTesla.com" on your localhost

1. Copy the /sites/_example.com/ folder to /sites/phpTesla.com
2. Update your hosts file with the line: "127.0.0.1 phpTesla.com.local" (note the .local)
3. Create a virtual host entry that points to /sites/phpTesla.com/www/index.php
   * Make sure you set the URL as "phpTesla.com.local"
4. Go to the URL "phpTesla.com.local" into your browser.

### Step #3 - Run the Site Installation

1. Go to "phpTesla.com.local/install" in your browser.
2. Follow the instructions provided - it will require configurations as you progress.
3. As the installation continues, several plugins will install to the database.