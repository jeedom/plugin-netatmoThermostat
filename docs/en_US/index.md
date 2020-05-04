Plugin for controlling Netatmo thermostats

Plugin configuration 
=======================

Once the Plugin is installed, you need to fill in your information
Netatmo connection :

-   **Client ID** : your client ID (see configuration section)

-   **Client secret** : your secret client (see configuration section)

-   **Username** : username of your netatmo account

-   **Password** : password for your Netatmo account

-   **Use alternative design** : allows to use another
    design (see widget section)

-   **Synchroniser** : allows you to synchronize Jeedom with your account
    Netamo to automatically discover your Netamo equipment. AT
    do after saving the previous settings.

Retrieving connection information 
==========================================

To integrate your station, you must have a client\_id and a
client\_secret généré sur le site <http://dev.netatmo.com>.

Once on click on start :

![netatmoWeather10](../images/netatmoWeather10.png)

Then on "create an app"

![netatmoWeather11](../images/netatmoWeather11.png)

Identify yourself, with your email and password

![netatmoWeather12](../images/netatmoWeather12.png)

Fill in the "Name" and "Description" fields (whatever you want
put it doesn't matter) :

![netatmoWeather13](../images/netatmoWeather13.png)

Then at the bottom of the page check the box "I accept the terms of use"
then click on "Create"

![netatmoWeather14](../images/netatmoWeather14.png)

Retrieve the "CLient id" and "Secret client" information and copy the
in the configuration part of the Plugin in Jeedom (see chapter
previous)

![netatmoWeather15](../images/netatmoWeather15.png)

Equipment configuration 
=============================

The configuration of Netatmo equipment is accessible from the menu
plugin.

> **Tip**
>
> As in many places on Jeedom, place the mouse on the far left
> brings up a quick access menu (you can
> from your profile always leave it visible).

Here you find all the configuration of your equipment :

-   **Name of the Netatmo device** : name of your Netatmo equipment

-   **Parent object** : indicates the parent object to which belongs
    equipment

-   **Activer** : makes your equipment active

-   **Visible** : makes it visible on the dashboard

-   **Identifiant** : unique equipment identifier

-   **Type** : type of your equipment (station, indoor probe,
    outdoor probe…)

Below you find the list of orders :

-   the name displayed on the dashboard

-   Historize : allows to historize the data

-   advanced configuration (small notched wheels) : Displays
    the advanced configuration of the command (method
    history, widget…)

-   Test : Used to test the command

> **Tip**
>
> When changing the widget mode it is advisable to click on
> synchronize to see the result immediately

FAQ 
===

What is the refresh rate ?
The system retrieves information every 15 min.


