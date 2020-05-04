Plugin para controle de termostatos Netatmo

Configuração do plugin 
=======================

Após a instalação do plug-in, é necessário preencher suas informações
Conexão Netatmo :

-   **ID do cliente** : seu ID de cliente (consulte a seção de configuração)

-   **Cliente secreto** : seu cliente secreto (consulte a seção de configuração)

-   **Nome de Usuário** : nome de usuário da sua conta netatmo

-   **Senha** : senha para sua conta Netatmo

-   **Usar design alternativo** : permite usar outro
    design (consulte a seção do widget)

-   **Synchroniser** : permite sincronizar o Jeedom com sua conta
    Netamo para descobrir automaticamente seu equipamento Netamo. Um
    faça depois de salvar as configurações anteriores.

Recuperando informações de conexão 
==========================================

Para integrar sua estação, você deve ter um cliente\_id e um
client\_secret généré sur le site <http://dev.netatmo.com>.

Uma vez clique em Iniciar :

![netatmoWeather10](../images/netatmoWeather10.png)

Em seguida, "crie um aplicativo"

![netatmoWeather11](../images/netatmoWeather11.png)

Identifique-se, com seu email e senha

![netatmoWeather12](../images/netatmoWeather12.png)

Preencha os campos "Nome" e "Descrição" (o que você quiser
coloque isso não importa) :

![netatmoWeather13](../images/netatmoWeather13.png)

Em seguida, na parte inferior da página, marque a caixa "Aceito os termos de uso"
depois clique em "Criar"

![netatmoWeather14](../images/netatmoWeather14.png)

Recupere as informações "ID do cliente" e "Cliente secreto" e copie o
na parte de configuração do plug-in no Jeedom (consulte o capítulo
anterior)

![netatmoWeather15](../images/netatmoWeather15.png)

Configuração do equipamento 
=============================

A configuração do equipamento Netatmo pode ser acessada no menu
plugin.

> **Tip**
>
> Como em muitos lugares em Jeedom, coloque o mouse na extremidade esquerda
> abre um menu de acesso rápido (você pode
> do seu perfil, deixe-o sempre visível).

Aqui você encontra toda a configuração do seu equipamento :

-   **Nome do dispositivo Netatmo** : nome do seu equipamento Netatmo

-   **Objeto pai** : indica o objeto pai ao qual pertence
    o equipamento

-   **Activer** : torna seu equipamento ativo

-   **Visible** : torna visível no painel

-   **Identifiant** : identificador único de equipamento

-   **Type** : tipo de seu equipamento (estação, sonda interna,
    sonda externa ...)

Abaixo você encontra a lista de pedidos :

-   o nome exibido no painel

-   Historicizar : permite historiar os dados

-   configuração avançada (pequenas rodas dentadas) : permite exibir
    a configuração avançada do comando (método
    história, widget ...)

-   Teste : permite testar o comando

> **Tip**
>
> Ao alterar o modo do widget, é recomendável clicar em
> sincronizar para ver o resultado imediatamente

Faq 
===

Qual é a taxa de atualização ?
O sistema recupera informações a cada 15 minutos.


