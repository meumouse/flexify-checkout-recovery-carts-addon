# Flexify Checkout - Recuperação de carrinhos abandonados

Recupere carrinhos e pedidos abandonados com follow up cadenciado. Plugin adicional do Flexify Checkout para WooCommerce.

---

### Agendador de tarefas

O plugin permite escolher entre o WP-Cron padrão ou o agendador em PHP baseado na biblioteca `peppeocchi/php-cron-scheduler`.

1. Em **Configurações → Geral → Agendador de tarefas** selecione **PHP-Cron** para utilizar um cron job real e evitar filas presas à visitação do site.
2. Execute o comando abaixo para configurar um cron job no servidor (A cada 5 minutos por padrão):

   ```bash
   wp fcrc scheduler --loop
   ```

   O comando usa a biblioteca para processar a fila de notificações e as rotinas de limpeza. Ele **não** adiciona a entrada ao crontab automaticamente; utilize `crontab -e` ou o agendador do seu provedor e, se preferir, execute `wp fcrc scheduler --print-cron` para obter uma sugestão de linha já formatada com o caminho do WordPress.

3. Enquanto o cron dedicado não estiver configurado, o plugin executará a fila nas requisições web para garantir que as notificações continuem funcionando. Também é possível manter o processo ativo continuamente com `wp fcrc scheduler --loop`, caso você utilize systemd, supervisord ou serviço equivalente.

---

### Instalação:

#### Instalação via painel de administração:

Você pode instalar um plugin WordPress de duas maneiras: via o painel de administração do WordPress ou via FTP. Aqui estão as etapas para ambos os métodos:

* Acesse o painel de administração do seu site WordPress.
* Vá para “Plugins” e clique em “Adicionar Novo”.
* Digite o nome do plugin que você deseja instalar na barra de pesquisa ou carregue o arquivo ZIP do plugin baixado.
* Clique em “Instalar Agora” e espere até que o plugin seja instalado.
* Clique em “Ativar Plugin”.

#### Instalação via FTP:

* Baixe o arquivo ZIP do plugin que você deseja instalar.
* Descompacte o arquivo ZIP em seu computador.
* Conecte-se ao seu servidor via FTP.
* Navegue até a pasta “wp-content/plugins”.
* Envie a pasta do plugin descompactada para a pasta “plugins” no seu servidor.
* Acesse o painel de administração do seu site WordPress.
* Vá para “Plugins” e clique em “Plugins Instalados”.
* Localize o plugin que você acabou de instalar e clique em “Ativar”.
* Após seguir essas etapas, o plugin deve estar instalado e funcionando corretamente em seu site WordPress.

---

### Registro de alterações (Changelogs):

Versão 1.3.7 (29/12/2025)
* Correção de bugs:
    - Prevenção contra erro fatal ao tentar recuperar carrinho

Versão 1.3.6 (17/12/2025)
* Correção de bugs
    - Cancelamento de eventos de follow up agendados após receber um pedido
    - Notificação de atualização disponível

Versão 1.3.5 (14/12/2025)
* Correção de bugs
    - Prevenir restauração do carrinho após ciclo ter finalizado (Recuperado, Comprou, Concluído, Pedido abandonado, Perdido)
    - Link da aba de navegação da tabela "Todos os carrinhos"
* Otimizações
* Recurso adicionado: Intervalo de envio de mensagens para follow ups

Versão 1.3.4 (12/12/2025)
* Correção de bugs
    - Exclusão de itens de follow up
* Otimizações

Versão 1.3.3 (29/10/2025)
* Alteração da API de consulta de atualizações

Versão 1.3.2 (18/10/2025)
* Correção de bugs:
    - Data e hora com padrão GMT
* Otimizações
* Recurso adicionado: Agendador de tarefas PHP-Cron
* Recurso adicionado: Enviar dados de eventos via Webhook

Versão 1.3.0 (08/07/2025)
* Correção de bugs
* Otimizações
    - Não registrar evento Cron se carrinho for de visitante anônimo
    - Preenchimento de dados do lead através do IP
* Recurso adicionado: Configurar API de coleta de localização através do IP
* Recurso adicionado: Painel de análise de métricas
* Recurso adicionado: Variável de texto {{ cart_total }} para recuperar o valor total do carrinho ou pedido vinculado
* Recurso adicionado: Variável de texto {{ products_list }} para recuperar a lista de produtos do carrinho ou pedido vinculado

Versão 1.2.0 (10/04/2025)
* Correção de bugs
* Otimizações
* Recurso removido: Intervalo de requisições
* Recurso adicionado: Exclusão de carrinhos aninomos após 1 hora
* Recurso adicionado: Exclusão de cupons expirados

Versão 1.1.2 (24/03/2025)
* Correção de bugs
    Criação do carrinho apenas se usuário tem produtos no carrinho
* Otimizações

Versão 1.1.0 (24/03/2025)
* Correção de bugs
* Otimizações
* Recurso adicionado: Intervalo de requisições

Versão 1.0.2 (10/03/2025)
* Recurso adicionado: Rotina de verificação de atualizações

Versão 1.0.1 (10/03/2025)
* Correção de bugs
* Otimizações
* Recurso adicionado: Ativar coleta de localização através do IP

Versão 1.0.0 (06/03/2024)
* Versão inicial