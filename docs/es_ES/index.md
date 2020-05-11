Plugin para controlar termostatos Netatmo

Configuración del plugin 
=======================

Una vez que se instala el complemento, debe completar su información
Conexión Netatmo :

-   **ID del cliente** : su ID de cliente (vea la sección de configuración)

-   **Client secret** : su cliente secreto (vea la sección de configuración)

-   **Nombre de usuario** : nombre de usuario de su cuenta netatmo

-   **Contraseña** : contraseña para su cuenta de Netatmo

-   **Usa un diseño alternativo** : permite usar otro
    diseño (ver sección de widgets)

-   **Sincronizar** : le permite sincronizar Jeedom con su cuenta
    Netamo para descubrir automáticamente su equipo Netamo. Un
    hacer después de guardar la configuración anterior.

Recuperando información de conexión 
==========================================

Para integrar su estación, debe tener un cliente\_id y un
client\_secret généré sur le site <http://dev.netatmo.com>.

Una vez que haga clic en Inicio :

![netatmoWeather10](../images/netatmoWeather10.png)

Luego en "crear una aplicación"

![netatmoWeather11](../images/netatmoWeather11.png)

Identifícate con tu correo electrónico y contraseña

![netatmoWeather12](../images/netatmoWeather12.png)

Complete los campos "Nombre" y "Descripción" (lo que desee
ponlo no importa) :

![netatmoWeather13](../images/netatmoWeather13.png)

Luego, en la parte inferior de la página, marque la casilla "Acepto los términos de uso"
luego haga clic en "Crear"

![netatmoWeather14](../images/netatmoWeather14.png)

Recupere la información de "ID de cliente" y "Cliente secreto" y copie el
en la parte de configuración del complemento en Jeedom (ver capítulo
anterior)

![netatmoWeather15](../images/netatmoWeather15.png)

Configuración del equipo 
=============================

Se puede acceder a la configuración del equipo Netatmo desde el menú
plugin.

> **Punta**
>
> Como en muchos lugares de Jeedom, coloca el mouse en el extremo izquierdo
> abre un menú de acceso rápido (puedes
> desde tu perfil siempre déjalo visible).

Aquí encontrarás toda la configuración de tu equipo :

-   **Nombre del dispositivo Netatmo** : nombre de su equipo Netatmo

-   **Objeto padre** : indica el objeto padre al que pertenece
    equipo

-   **Activar** : activa su equipo

-   **Visible** : lo hace visible en el tablero

-   **Identificador** : identificador de equipo único

-   **Tipo** : tipo de su equipo (estación, sonda interior,
    sonda exterior ...)

A continuación encontrará la lista de pedidos :

-   el nombre que se muestra en el tablero

-   Guardar historial : permite historizar los datos

-   configuración avanzada (ruedas con muescas pequeñas) : Muestra
    La configuración avanzada del comando (método
    historia, widget ...)

-   Probar : Se usa para probar el comando

> **Punta**
>
> Al cambiar el modo del widget, es recomendable hacer clic en
> sincronizar para ver el resultado inmediatamente

Preguntas frecuentes 
===

¿Cuál es la frecuencia de actualización? ?
El sistema recupera información cada 15 minutos.


