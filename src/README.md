CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Maintainers


INTRODUCTION
------------

Ezpizee Connector intends to be used for integrating Ezpizee, SaaS based e-commerce platform, with WordPress,
providing a fast and full access to all administration links.

 * For a full description of the module, visit the project page:
   https://github.com/ezpizee/WordPressConnector

 * To submit bug reports and feature suggestions, or to track changes:
   https://github.com/ezpizee/WordPressConnector/issues


REQUIREMENTS
------------

 * PHP 7.3.x
 * WordPress 4.x
 * Ezpizee's subscription


INSTALLATION
------------

1) Download one of our releases https://github.com/ezpizee/WordPressConnector/releases (the latest version recommended)
 
2) Unzip

3) Login to your WordPress Administrator section

4) Go to **Plugins > Add New > Upload Plugin > Choose File**

5) Select the **/dist/ezpizee.zip** from the unzipped folder

6) Click on the **Install Now**
 
7) Go to **Settings > Ezpizee** to install / configure your Ezpizee App for WordPress
   * Fill in the value for 
      * **Client ID** (obtain from https://www.ezpizee.com/en/user/admin-ui.html),
      * **Client Secret** (obtain from https://www.ezpizee.com/en/user/admin-ui.html),
      * **App Name** (a unique name that is not already been used in any other Ezpizee installation)
   * Select the Environment to integrate with Ezpizee (when not sure, select **Production**)
   * Click **Save Configuration**

8) Wait until the installation is done, then click on the **Ezpizee Portal**

MAINTAINERS
-----------

Current maintainers:
 * Sothea Nim - https://github.com/nimsothea
 * Sokhon Pang - https://github.com/pangkhon