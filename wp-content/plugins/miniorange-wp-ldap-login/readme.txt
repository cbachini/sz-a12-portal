=== Active Directory/LDAP Integration for Cloud & Shared Hosting Platforms ===
Contributors: miniOrange
Donate link: http://miniorange.com
Tags: active directory integration, ldap authentication, ldap directory, shared hosting, sso
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 5.6.0
Stable tag: 6.2.1
License: Expat
License URI: https://plugins.miniorange.com/mit-license

LDAP/Active Directory authentication for WordPress on shared hosting. No PHP extension required. Supports Azure AD, OpenLDAP, and more.

== Description ==

[Showcase](https://plugins.miniorange.com/wordpress-ldap-login-cloud?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms) | [Documentation](https://plugins.miniorange.com/step-by-step-guide-for-wordpress-ldap-login-cloud?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms) | [Features](https://plugins.miniorange.com/wordpress-ldap-login-cloud?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms#Features) | [Add-Ons](https://plugins.miniorange.com/wordpress-ldap-login-cloud?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms#Add-Ons) | [Contact Us](https://www.miniorange.com/contact?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms)

[Active Directory Integration / LDAP Integration](https://plugins.miniorange.com/step-by-step-guide-for-wordpress-ldap-login-cloud?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms) allows you to log in to your WordPress site hosted on any cloud / shared hosting platforms, such as Hostinger, Bluehost, DreamHost, HostGator, GreenGeeks, SiteGround, A2 Hosting, Flywheel, WPEngine, Kinsta, GoDaddy, by using the credentials stored in your Active Directory, such as Microsoft Active Directory, Azure Active Directory, OpenLDAP Directory, Sun Active Directory, JumpCloud, FreeIPA Directory, Synology, OpenDS, or other LDAP directories. This plugin does not require you to have the LDAP extension enabled and is a safe way of performing authentication for your WordPress site.

**How can we Integrate LDAP/Active Directory with WordPress Shared Hosting?**
Integrating LDAP/Active Directory with a WordPress shared hosting platform is simple by using the miniOrange LDAP Integration plugin. This plugin enables users to log in securely using credentials from directories such as Microsoft AD, Azure AD, and OpenLDAP, without requiring the PHP LDAP extension. After installing the plugin, configure your Active Directory settings, map LDAP attributes to WordPress user profiles, and authenticate users from multiple Active Directories for seamless and secure access to your WordPress site.

= Features:- =

* Perform LDAP / AD Login into your WordPress website hosted on a shared hosting environment/cloud platform using Active Directory / other LDAP directory credentials.
* Supports LDAP / AD authentication for WordPress site(s) hosted on Cloud / Shared Hosting Platforms such as Hostinger, Bluehost, DreamHost, HostGator, GreenGeeks, SiteGround, A2 Hosting, Flywheel, WPEngine, Kinsta, GoDaddy, etc.
* No Need to install/configure any additional extension (like PHP LDAP extension) on the cloud / shared hosting platform to facilitate a connection to the Active Directory / other LDAP directories.
* <strong>Automatic User Registration in WordPress:</strong> Automatically register LDAP/AD user(s) in your WordPress website after a successful LDAP / AD login.
* <strong>Multiple Search Containers: </strong>Authenticate users against multiple search bases from your LDAP Active Directory / any other LDAP directory.
* <strong>Login With Any LDAP Attribute Of Your Choice: </strong>Authenticate users against multiple LDAP / Active Directory username attributes like sAMAccountName, UID, UserPrincipalName, mail, cn, or any other custom LDAP attribute(s) according to your LDAP Active Directory / any other LDAP directory.
* <strong>Custom LDAP to WordPress Profile Mapping: </strong>Mapping of your AD / other LDAP directory user profile attributes to the WordPress user profile. Upon login, WordPress user profile information will automatically be updated depending on the LDAP / Active Directory user profile attributes.
* <strong>Role Mapping: </strong>Mapping of LDAP groups from your AD / other LDAP directory to WordPress Roles upon LDAP authentication.
* <strong>Advanced Attribute Mapping: </strong>Map user profile information to be retrieved from Active Directory / other LDAP directory. eg: first name, last name, phone number, email, etc. Configure any LDAP / AD attribute such as givenname, sn, uid, mail, telephone number, department, company, sAMAccountName, UserPrincipalName, etc, to WordPress user profile attributes.
* <strong>Redirect to custom URL after WordPress LDAP authentication: </strong>Our LDAP Active Directory Integration plugin for Cloud & Shared Hosting Platforms allows you to redirect users to a WordPress Profile page, Home page, or any other Custom URL after successful LDAP authentication from your AD / other LDAP directory.
* <strong>Advanced Role Mapping:</strong> Assign specific WordPress roles based on the LDAP / Active Directory group memberships or the Organizational Units (OU’s), which are set in the LDAP Server / Active Directory. Using our LDAP AD integration plugin, you can assign a default WordPress role to all the LDAP / Active Directory users.
* <strong>Supports both LDAP and LDAPS (LDAP Secure Connection) connection:</strong> This plugin supports LDAP / Active Directory Login Integration for WordPress via LDAP and LDAPS protocol for safe and secure authentication.
* <strong>Authenticate users from LDAP and WordPress:</strong> Enable AD Login for LDAP / Active Directory users only. Also, allow login for both LDAP and WordPress users.
* <strong>Support WordPress Password as a fallback:</strong> Fallback to the local WordPress password if Active Directory / other LDAP Directory is temporarily unreachable.
* <strong>Supports Custom Search Filter:</strong> This WordPress LDAP plugin allows you to configure a custom search filter for LDAP / AD login. You can restrict user(s) from logging in depending on the user group(s), userAccountControl, etc.
* <strong>Support for Import / Export Plugin Configuration:</strong> Export your WordPress LDAP plugin configuration from the staging/testing site and import it to the production / live site. This will save you the hassle of reconfiguring the LDAP plugin.
* <strong>LDAP Authentication Logs Report:</strong> The plugin provides a feature to records detailed logs of all failed LDAP/AD Login attempts.
* Test authentication using credentials stored in your Active Directory / other LDAP directory.
* We provide active support for configuring your Active Directory / other LDAP directory.
* Keep the user’s profile information in sync with the AD / other LDAP Directories upon every WordPress LDAP authentication.
* Works perfectly with third-party plugins such as BuddyBoss, BuddyPress, Ultimate Member, Gravity Forms, Groups, and eMember.
* Compatible with the latest versions of WordPress and PHP.
* <strong>Multisite Support: </strong> The plugin also supports LDAP Active Directory Login integration for multisite environments. [PREMIUM]
* <strong>Multiple LDAP/AD Support: </strong> Authenticate users against multiple Active Directories/ other LDAP directories. [PREMIUM]
* <strong>Active Support provided:</strong> miniOrange provides 24/7 support to all our customers. We have a team of dedicated developers who can help you debug any issues you may face to keep your WordPress site always live and functioning. You can reach out to us at ldapsupport@xecurify.com
* We provide extensive, easy-to-understand [documentation](https://plugins.miniorange.com/step-by-step-guide-for-wordpress-ldap-login-cloud?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms) and [YouTube setup videos](https://www.youtube.com/watch?v=6OkcHs-Kdx4&utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms) to assist you while configuring our LDAP Active Directory Integration (AD Integration) Plugin for Cloud / Shared Hosting Platform.

**You can find out how to configure the plugin through the video below**

https://www.youtube.com/watch?v=6OkcHs-Kdx4

= [Add-ons List](https://plugins.miniorange.com/wordpress-ldap-login-cloud?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms#Add-Ons)=

* <strong>Auto-Login(SSO) for shared hosting:</strong> Using our Auto-Login(SSO) add-on, the user can perform an auto-login / Single Sign On from Domain Joined Machines into their WordPress site, which is hosted on a cloud / shared hosting environment.
* <strong>Sync Users LDAP Directory:</strong> Sync the users present in your LDAP Active Directory to your WordPress site. Schedules can be configured for when the LDAP AD sync should run and can even be set to perform the LDAP AD sync after a specific interval.
* <strong>Page/Post Link Restriction:</strong> Restrict user access to pages or posts on your WordPress website based on the WordPress roles assigned to a specific user. This allows you to make the website accessible only to logged-in users.
* <strong>Customize WordPress Login Page:</strong> Customize your WordPress login page by adding your own logo and displaying a custom message(s).
* <strong>BuddyPress Extended Profile Integration for Shared Hosting:</strong>Integrates users’ BuddyPress extended profile attributes with their LDAP Active Directory attributes upon LDAP AD Login.
* <strong>Profile Picture Sync Add-on:</strong> Update the WordPress user profile picture with the thumbnail photo stored in your Active Directory / LDAP server.
* <strong>Third Party Plugin User Profile Integration:</strong> Update the user profiles created using any third-party plugins with the attributes present in your Active Directory / LDAP Server.
* <strong>WP Group Plugin Integration:</strong> Assign users to WordPress groups based on their group memberships present in the Active Directory. You can map any number of WordPress groups to the LDAP / AD groups.
* <strong>Ultimate Member Login Integration:</strong> Using your LDAP credentials, log in to Ultimate Member and integrate your Ultimate Member User Profile with the attributes present in your LDAP Active Directory.

= Other Use-Cases we support :- =
* <strong>[miniOrange WP LDAP/AD Login for Intranet sites plugin](https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms)</strong> supports LDAP login to WordPress sites using credentials stored in Active Directory and LDAP Directory systems. Only if you have access to the <strong>[LDAP Extension](https://faq.miniorange.com/knowledgebase/how-to-enable-php-ldap-extension/?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms)</strong> on your site.
* <strong> [Search Staff/Employee present in your Active Directory](https://plugins.miniorange.com/wordpress-ldap-directory-search?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms)</strong>: allows you to search and display the users present in your Active Directory / LDAP Server on a WordPress page using a shortcode.
* <strong>[WordPress Login and User Management Plugin](https://plugins.miniorange.com/wordpress-login-and-user-management-plugin?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms)</strong>: This plugin offers several functionalities, including bulk user management, user redirection based on WordPress roles, user session management, auto-logout users, and the ability to make a page or post private or public based on an ID or URL.
* miniOrange also supports <Strong>[VPN use cases](https://www.miniorange.com/iam/solutions/vpn-mfa-multi-factor-authentication?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms)</Strong> Log in to your VPN client using Active Directory /other LDAP Directory credentials and <strong>[Multi-Factor Authentication](https://www.miniorange.com/products/multi-factor-authentication-mfa?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms)</strong>.
* miniOrange supports <Strong>[API Security use cases](https://apisecurity.miniorange.com/xecureapi-unified-api-gateway-and-security-solutions/?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms)</Strong> to protect and secure your APIs using our product <strong>[XecureAPI](https://apiconsole.miniorange.com/account/login?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms)</strong> which helps you to enable Authentication methods ( like OAuth, SAML, LDAP, API Key Authentication, JWT Authentication etc ), Rate Limiting, IP restriction, and much more on your APIs for complete protection.
* miniOrange supports <strong>[Single-Sign-On (SSO)](https://www.miniorange.com/products/single-sign-on-sso?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms)</strong> into a plethora of applications and supports various protocols like <strong>[RADIUS](https://www.miniorange.com/blog/radius-server-authentication/?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms), [SAML](https://plugins.miniorange.com/wordpress-single-sign-on-sso?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms), [OAuth](https://plugins.miniorange.com/wordpress-sso?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms), [LDAP/LDAPS](https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms)</strong>, using various IDPs like <strong>Azure Active Directory, Microsoft On-Premise Active Directory, Octa, ADFS</strong>, etc.
* Contact us at <strong>info@xecurify.com</strong> to know more.

= Do you want support? =
Please email us at <strong>info@xecurify.com</strong> or <a href="https://www.miniorange.com/contact?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms" target="_blank">Contact us</a>

== Installation ==

= From your WordPress dashboard =
1. Visit `Plugins > Add New`
2. Search for `Active Directory Integration for Cloud & Shared Hosting`. Find and Install `Active Directory Integration for Cloud & Shared Hosting Environment`
3. Activate the plugin from your Plugins page.

= From WordPress.org =
1. Download Active Directory/LDAP Integration for Cloud & Shared Hosting Platforms.
2. Unzip and upload the `miniorange-wp-ldap-login` directory to your `/wp-content/plugins/` directory.
3. Activate `Active Directory/LDAP Integration for Cloud & Shared Hosting Platforms` from your Plugins page.

= Once Activated =
1. Go to `Settings-> LDAP Login Config`, and follow the instructions.
2. Click on `Save`

Make sure that if there is a firewall, you `OPEN THE FIREWALL` to allow incoming requests to your LDAP. Please open port 389(636 for SSL or ldaps). Host - 52.55.147.107 - This is the host from where the LDAP connection, as well as authentication requests, are going to be made.

== Frequently Asked Questions ==

=Does the LDAP Integration plugin require installing the PHP LDAP extension on my cloud or shared hosting platform?=
No, the plugin does not require installing or configuring the PHP LDAP extension on your hosting platform. It connects securely to your Active Directories, such as Active Directory, without needing extra extensions.

=What types of LDAP attributes can be mapped to WordPress profiles?=
The plugin allows mapping LDAP/Active Directory attributes such as sAMAccountName, UID, UserPrincipalName, email, and custom attributes to WordPress user profiles. These attributes can also be automatically updated upon login.

=Can I assign WordPress roles based on LDAP group memberships?=
Yes, the plugin supports role mapping, where LDAP group memberships or organizational units (OUs) in the directory can be mapped to specific WordPress roles. This allows you to control user roles dynamically upon authentication.

=Is there support for multiple LDAP/Active Directory integrations in the plugin?=
Yes, the plugin offers support for multiple LDAP or Active Directory integrations, including authentication from multiple directories. This feature is available in the premium version of the plugin.

=What kind of support does miniOrange provide for the LDAP/Active Directory Integration plugin?=
miniOrange offers 24/7 active support, including help with plugin configuration and debugging. Additionally, they provide extensive documentation, YouTube setup tutorials, and email support at ldapsupport@xecurify.com.


= How should I enter my LDAP configuration? I only see Register with miniOrange. =
Our very simple and easy registration lets you register with miniOrange. Our LDAP Active Directory Integration plugin for Cloud and Shared Hosting Platforms works if you are connected to miniOrange. Once you have registered with a valid email address and phone number, you will be able to add your Active Directory / LDAP directory configuration.

= I am not able to get the configuration right. =
Make sure that if there is a firewall, you `OPEN THE FIREWALL` to allow incoming requests to your Active Directory / LDAP Directory. Please open port 389(636 for SSL or ldaps). Host - 52.55.147.107 - This is the host from where the Active Directory / LDAP connection, as well as authentication requests, are going to be made. For further help, please click on the Troubleshooting button. Check the steps to see what you could have missed. If that does not help, please check the format of the demo settings. You can copy them over using `Copy Default Config`.

= I am locked out of my account and can't log in with either my WordPress credentials or LDAP credentials. What should I do? =
Firstly, please check if the `user you are trying to login with` exists in your WordPress. To unlock yourself, rename miniorange-wp-ldap-login plugin name. You will be able to login with your WordPress credentials. After logging in, rename the plugin back to miniorange-wp-ldap-login. If the problem persists, `activate, deactivate and again activate` the plugin.

= LDAP / Active Directory configurations are correct and test authentication is also working but the active directory login is not working on the website. =
Please make sure that the active directory login is enabled. You can check this by going to the <strong>"Sign-In Settings"</strong> Tab. The <strong>"Enable LDAP login"</strong> checkbox should be checked. This will enable users to use Active Directory login / LDAP Login on the WordPress website.

= I am not able to connect my AD / LDAP Server over LDAPS connection.
Follow our <a href="https://www.miniorange.com/guide-to-setup-ldaps-on-windows-server" target="_blank">step by step guide</a> to enable LDAPS on your Active Directory and download the LDAPS certificate of your AD. Once you download your certificate, you need to get it uploaded on our miniOrange Cloud IDP Servers.
In order to get you certificate uploaded, please email us at ldapsupport@xecurify.com OR <a href="https://www.miniorange.com/contact?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms" target="_blank">Contact us</a> so we can help you further.

= For support or troubleshooting help =
Please email us at <strong>info@xecurify.com</strong> or <a href="https://www.miniorange.com/contact?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms" target="_blank">Contact us</a>.

We can add the provision of user management, such as creating users not present in WordPress from LDAP Server, adding users, editing users, and so on. For further details, please email us at <strong>info@xecurify.com</strong> or <a href="https://www.miniorange.com/contact?utm_source=wordpress%20readme&utm_medium=marketplace&utm_campaign=Active%20Directory/LDAP%20Integration%20for%20Cloud%20&%20Shared%20Hosting%20Platforms" target="_blank">Contact us</a>.

== Screenshots ==

1. Active Directory/LDAP Server Configuration
2. Active Directory/LDAP Group(s) to WordPress User Role Mapping
3. Active Directory/LDAP Attribute to WordPress User Profile Attribute Mapping
4. LDAP/AD Login Integration Login Settings Configuration
5. LDAP/AD Login Integration Import/Export Configuration Settings
6. LDAP/AD Authentication Report
7. miniOrange Premium Add-Ons for LDAP/AD Integration Login for Cloud & Shared Hosting Platforms

== Changelog ==

= 6.2.1 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Compatibility with WordPress 7
 
= 6.2.0 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Compatibility with WordPress 6.9
 * Security fixes and improvements
 * General bug fixes
 * Scheduled log cleanup

= 6.1.3 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Readme changes

= 6.1.2 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Usability Improvements

= 6.1.1 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Compatibility with WordPress 6.8

= 6.1.0 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Added Role-based Login Restriction
 * UI Improvements
 * Compatibility with WordPress 6.7

= 6.0.1 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * UI Improvements
 * Readme changes

= 6.0.0 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Enhanced User Reports for better usability.
 * Revamped the User Interface to provide a user-friendly experience.
 * Security Fixes.
 * Compatibility with WordPress 6.6

= 5.4.1 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Code optimization.
 * Compatibility with WordPress 6.5

= 5.4.0 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 *Added Authentication Audit Logs Functionality.


= 5.3.2 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Compatibility with WordPress 6.4

= 5.3.1 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * UI Improvements.

= 5.3.0 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Changed plugin code structure.
 * Code Optimization.

= 5.2.2 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Minor UI Fix.
 * Compatibility with WordPress version 6.3.

= 5.2.1 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Added feature to allow admin to make any page(s) publicly accessible.
 * Compatibility with WordPress version 6.2.

= 5.2.0 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * WP Guideline & Security Fixes.
 * Code Optimization.

= 5.1.2 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Advertisement of Christmas Offers.
 * Usability Improvements.

= 5.1.1 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Usability Improvements.
 * Minor UI changes.

= 5.1.0 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Added feature to Import/Export plugin configuration.
 * Code Optimization and Security fixes.
 * Compatibility with WordPress version 6.1.
 * Fixed License Update issue.

= 5.0.3 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Added a video for step-by-step configuration of the Plugin.
 * Improved Account Registration form.
 * Code Optimization.

= 5.0.2 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Added a Debugger section for troubleshooting issues.
 * UI Enhancement.

= 5.0.1 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * UI Fixes.
 * Added feature to auto select default LDAP username attribute when LDAP server type is selected.

= 5.0 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * UI Enhancement.
 * Compatibility with miniOrange On-Premise LDAP Gateway.
 * Fixed Issue for allowing LDAP Login using password with special characters.
 * Code Optimization.
 * Security Improvements.

= 4.4 =
* Active Directory Integration for Shared Hosting :
 * Compatibility with WordPress version 6.0.
 * Code Optimization.
 * Security Improvements.

= 4.3 =
* Active Directory Integration for Shared Hosting :
 * Compatibility changes for BuddyPress Profile Integration Add-on.
 * Security fixes for Cloud Directory Sync Add-on.
 * Compatibility with WordPress version 5.9.3.
 * Fixes for checking current license details.

= 4.2.2 =
* Active Directory Integration for Shared Hosting :
 * Updated troubleshooting information.
 * Compatibility with WordPress version 5.9.2.

= 4.2.1 =
* Active Directory Integration for Shared Hosting :
 * Updated Troubleshooting FAQ.

= 4.2 =
* Active Directory Integration for Shared Hosting :
 * Automatic search-filter creation based on selected LDAP attributes.
 * Compatibility with WordPress version 5.9.

= 4.1.91 =
* Active Directory Integration for Shared Hosting :
 * New Year Offers.
 * Code Optimization.

= 4.1.9 =
* Active Directory Integration for Shared Hosting :
 * Christmas Offers
 * Updated troubleshooting information.

= 4.1.8 =
* Active Directory Integration for Shared Hosting :
 * Code Optimization

= 4.1.7 =
* Active Directory Integration for Shared Hosting :
 * Bug Fixes - Sanitization of input fields.
 * Code Optimization
 * Removed curl calls and replaced with wp_remote_post call
 * Usability Improvements.

= 4.1.6 =
* Active Directory Integration for Shared Hosting :
 * Added administrator role option in the default role mapping selection.

= 4.1.5 =
* Active Directory Integration for Shared Hosting :
 * Added option to allow both LDAP and WordPress Users/WordPress Admin Users to login.

= 4.1.4 =
* Active Directory Integration for Shared Hosting :
 * Updated troubleshooting information.
 * Updated licensing plans.

= 4.1.3 =
* Active Directory Integration for Shared Hosting :
 * Updated troubleshooting information.

= 4.1.2 =
* Active Directory Integration for Shared Hosting :
 * Compatibility with WordPress 5.8.
 * Usability Improvements.

= 4.1.1 =
* Active Directory Integration for Shared Hosting :
 * Integrated a support form for scheduling a call for assistance.

= 4.1 =
* Active Directory Integration for Shared Hosting :
 * Usability Improvements.

= 4.0.9 =
* Active Directory Integration for Shared Hosting :
 * Usability Improvements.

= 4.0.8 =
* Active Directory Integration for Shared Hosting :
 * Usability Improvements.

= 4.0.7 =
* Active Directory Integration for Shared Hosting :
 * Usability Improvements.

= 4.0.6 =
* Active Directory Integration for Shared Hosting :
 * Usability Improvements.

= 4.0.5 =
* Active Directory Integration for Shared Hosting :
 * Tested for WordPress 5.7.
 * Compatibility Fixes for PHP 8.0.
 * Usability Improvements.

= 4.0.4 =
* Active Directory Integration for Shared Hosting :
 * Usability Improvements.
 * Licensing Update.

= 4.0.3 =
* Active Directory Integration for Shared Hosting :
 * Added Separate Tab for Sign In settings.
 * Usability Improvements.

= 4.0.2 =
* Active Directory Integration for Shared Hosting :
 * Compatible with WordPress 5.6.

= 4.0.1 =
* Active Directory Integration for Shared Hosting :
 * Usability improvements.

= 4.0 =
* Active Directory Integration :
 * Usability improvements.

= 3.9.2 =
* Active Directory Integration :
 * Usability improvements.

= 3.9.1 =
* Active Directory Integration :
 * Usability improvements.

= 3.9 =
* Active Directory Integration :
 * Added Protect all website content by login and Redirect after authentication.

= 3.8 =
* Active Directory Integration :
 * Compatibility and PHPv7.4 fixes

= 3.72 =
* Active Directory Integration :
 * Attribute Mapping fix

= 3.7 =
* Active Directory Integration :
 * Host changes with fallback

= 3.6 =
* Active Directory Integration :
 * Removed MCrypt dependency

= 3.52 =
* Active Directory Integration :
 * Authorization header fixes for OTP APIs

= 3.50 =
* Active Directory Integration :
 * Authorization header fixes for API calls

= 3.50 =
* Active Directory Integration :
 * Authorization headers in cURL Requests

= 3.44 =
* Active Directory Integration :
 * Name fix

= 3.43 =
* Active Directory Integration :
 * Name change

= 3.42 =
* Active Directory Integration :
 * Increased priority for authentication hook

= 3.41 =
* Active Directory Integration :
 * Removed deprecated function calls

= 3.4 =
* Active Directory Integration :
 * Bug fix in login

= 3.3 =
* Active Directory Integration :
 * Bug fix in profile update

= 3.2 =
* Active Directory Integration :
 * Tested with WordPress 4.6

= 3.1 =
* Active Directory Integration :
 * Licensing update

= 3.0 =
* Active Directory Integration :
 * Added custom attribute mapping

= 2.7 =
* Registration fixes

= 2.6 =
* Added LDAP Group to WP Role Mapping

= 2.5 =
* Added Attributes Mapping

= 2.4 =
* Added troubleshooting page.

= 2.3 =
* Added Ping to LDAP Server. Usability fixes.

= 2.2 =
* New feature - Added Auto Registration of users post LDAP authentication

= 2.1.2 =
* Bug fixes

= 2.1.1 =
* Added additional error handling and bug fixes.

= 2.1 =
* Bug fixes and added user verification

= 2.0.2 =
* Usability fixes

= 2.0.1 =
* Bug fix

= 2.0.0 =
* LDAP usability fixes

= 1.0.0 =
* this is the first release.

== Upgrade Notice ==

= 6.2.1 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Compatibility with WordPress 7

= 6.2.0 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Compatibility with WordPress 6.9
 * Security fixes and improvements
 * General bug fixes
 * Scheduled log cleanup

= 6.1.3 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Readme changes

= 6.1.2 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Usability Improvements

= 6.1.1 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Compatibility with WordPress 6.8

= 6.1.0 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Added Role-based Login Restriction
 * UI Improvements
 * Compatibility with WordPress 6.7

= 6.0.1 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * UI Improvements
 * Readme changes

= 6.0.0 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Enhanced User Reports for better usability.
 * Revamped the User Interface to provide a user-friendly experience.
 * Security Fixes.
 * Compatibility with WordPress 6.6

= 5.4.1 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Code optimization.
 * Compatibility with WordPress 6.5
 
= 5.4.0 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 *Added Authentication Audit Logs Functionality.
 
= 5.3.2 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Compatibility with WordPress 6.4

= 5.3.1 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * UI Improvements.

= 5.3.0 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Changed plugin code structure.
 * Code Optimization.

= 5.2.2 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Minor UI Fix.
 * Compatibility with WordPress version 6.3.

= 5.2.1 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Added feature to allow admin to make any page(s) publicly accessible.
 * Compatibility with WordPress version 6.2.

= 5.2.0 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * WP Guideline & Security Fixes.
 * Code Optimization.

= 5.1.2 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Advertisement of Christmas Offers.
 * Usability Improvements.

= 5.1.1 = 
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Usability Improvements.
 * Minor UI changes.

= 5.1.0 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Added feature to Import/Export plugin configuration.
 * Code Optimization and Security fixes.
 * Compatibility with WordPress version 6.1.
 * Fixed License Update issue.

= 5.0.3 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Added a video for step-by-step configuration of the Plugin.
 * Improved Account Registration form.
 * Code Optimization.

= 5.0.2 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * Added a Debugger section for troubleshooting issues.
 * UI Enhancement.

= 5.0.1 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * UI Fixes.
 * Added feature to auto select default LDAP username attribute when LDAP server type is selected.

= 5.0 =
* Active Directory LDAP Integration for Cloud & Shared Hosting :
 * UI Enhancement.
 * Compatibility with miniOrange On-Premise LDAP Gateway.
 * Fixed Issue for allowing LDAP Login using password with special characters.
 * Code Optimization.
 * Security Improvements.

= 4.4 =
* Active Directory Integration for Shared Hosting :
 * Compatibility with WordPress version 6.0.
 * Code Optimization.
 * Security Improvements.

= 4.3 =
* Active Directory Integration for Shared Hosting :
 * Compatibility changes for BuddyPress Profile Integration Add-on.
 * Security fixes for Cloud Directory Sync Add-on.
 * Compatibility with WordPress version 5.9.3.
 * Fixes for checking current license details.

= 4.2.2 =
* Active Directory Integration for Shared Hosting :
 * Updated troubleshooting information.
 * Compatibility with WordPress version 5.9.2.

= 4.2.1 =
* Active Directory Integration for Shared Hosting :
 * Updated Troubleshooting FAQ.

= 4.2 =
* Active Directory Integration for Shared Hosting :
 * Automatic search-filter creation based on selected LDAP attributes.
 * Compatibility with WordPress version 5.9.

= 4.1.91 =
* Active Directory Integration for Shared Hosting :
 * New Year Offers.
 * Code Optimization.

= 4.1.9 =
* Active Directory Integration for Shared Hosting :
 * Christmas Offers
 * Updated troubleshooting information.

= 4.1.8 =
* Active Directory Integration for Shared Hosting :
 * Code Optimization

= 4.1.7 =
* Active Directory Integration for Shared Hosting :
 * Bug Fixes - Sanitization of input fields.
 * Code Optimization
 * Removed curl calls and replaced with wp_remote_post call
 * Usability Improvements.

= 4.1.6 =
* Active Directory Integration for Shared Hosting :
 * Added administrator role option in the default role mapping selection.

= 4.1.5 =
* Active Directory Integration for Shared Hosting :
 * Added option to allow both LDAP and WordPress Users/WordPress Admin Users to login.

= 4.1.4 =
* Active Directory Integration for Shared Hosting :
 * Updated troubleshooting information.
 * Updated licensing plans.

= 4.1.3 =
* Active Directory Integration for Shared Hosting :
 * Updated troubleshooting information.

= 4.1.2 =
* Active Directory Integration for Shared Hosting :
 * Compatibility with WordPress 5.8.
 * Usability Improvements.

= 4.1.1 =
* Active Directory Integration for Shared Hosting :
 * Integrated a support form for scheduling a call for assistance.

= 4.1 =
* Active Directory Integration for Shared Hosting :
 * Usability Improvements.

= 4.0.9 =
* Active Directory Integration for Shared Hosting :
 * Usability Improvements.

= 4.0.8 =
* Active Directory Integration for Shared Hosting :
 * Usability Improvements.

= 4.0.7 =
* Active Directory Integration for Shared Hosting :
 * Usability Improvements.

= 4.0.6 =
* Active Directory Integration for Shared Hosting :
 * Usability Improvements.

= 4.0.5 =
* Active Directory Integration for Shared Hosting :
 * Tested for WordPress 5.7.
 * Compatibility Fixes for PHP 8.0.
 * Usability Improvements.

= 4.0.4 =
* Active Directory Integration for Shared Hosting :
 * Usability Improvements.
 * Licensing Update.

= 4.0.3 =
* Active Directory Integration for Shared Hosting :
 * Added Separate Tab for Sign In settings.
 * Usability Improvements.

= 4.0.2 =
* Active Directory Integration for Shared Hosting :
 * Compatible with WordPress 5.6.

= 4.0.1 =
* Active Directory Integration for Shared Hosting :
 * Usability improvements.

= 4.0 =
* Active Directory Integration :
 * Usability improvements.

= 3.9.2 =
* Active Directory Integration :
 * Usability improvements.

= 3.9.1 =
* Active Directory Integration :
 * Usability improvements.

= 3.9 =
* Active Directory Integration :
 * Added Protect all website content by login and Redirect after authentication.

= 3.8 =
* Active Directory Integration :
 * Compatibility and PHPv7.4 fixes

= 3.72 =
* Active Directory Integration :
 * Attribute Mapping fix

= 3.7 =
* Active Directory Integration :
 * Host changes with fallback

= 3.6 =
* Active Directory Integration :
 * Removed MCrypt dependency

= 3.52 =
* Active Directory Integration :
 * Authorization header fixes for OTP APIs

= 3.50 =
* Active Directory Integration :
 * Authorization header fixes for API calls

= 3.50 =
* Active Directory Integration :
 * Authorization headers in cURL Requests

= 3.44 =
* Active Directory Integration :
 * Name fix

= 3.43 =
* Active Directory Integration :
 * Name change

= 3.42 =
* Active Directory Integration :
 * Increased priority for authentication hook

= 3.41 =
* Active Directory Integration :
 * Removed deprecated function calls

= 3.4 =
* Active Directory Integration :
 * Bug fix in login

= 3.3 =
* Active Directory Integration :
 * Bug fix in profile update

= 3.2 =
* Active Directory Integration :
 * Tested with WordPress 4.6

= 3.1 =
* Active Directory Integration :
 * Licensing update

= 3.0 =
* Active Directory Integration :
 * Added custom attribute mapping

= 2.7 =
* Registration fixes

= 2.6 =
* Added LDAP Group to WP Role Mapping

= 2.5 =
* Added Attributes Mapping

= 2.4 =
* Added troubleshooting page.

= 2.3 =
* Added Ping to LDAP Server. Usability fixes.

= 2.2 =
* New feature - Added Auto Registration of users post LDAP authentication

= 2.1.2 =
* Bug fixes

= 2.1.1 =
* Added additional error handling and bug fixes.

= 2.1 =
* Bug fixes and added user verification

= 2.0.2 =
* Usability fixes

= 2.0.1 =
* Bug fix

= 2.0.0 =
* LDAP usability fixes

= 1.0 =
* First version of plugin.
