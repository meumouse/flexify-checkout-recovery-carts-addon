Versão 1.4.0 (27/05/2026)
* Correção de bugs
    - Pesquisa de carrinhos
    - Recuperação de produtos variáveis (atributos da variação eram perdidos em dados legados)
    - Link de recuperação sendo descartado pelo redirecionamento de carrinho vazio do WooCommerce
    - Persistência da sessão WooCommerce ao restaurar carrinho em guia anônima
    - Verificação `instanceof WC_Session` não totalmente qualificada (namespace) em Cart_Events, Order_Events e Helpers
    - Placeholder `{{ cart_total }}` enviava HTML em vez de texto formatado
    - Substituição de placeholders no construtor de mensagens do Joinotify
    - Loop de follow up ao reabandonar um carrinho (eventos já enviados eram reagendados)
    - Ação "Disparar evento agora" na fila de processamentos não enviava a mensagem (bypass das verificações de janela de envio e de compra)
    - Exclusão de itens de follow up
    - Recriação indevida de itens de follow up após exclusão
    - Falta da chave do evento em colunas de notificações
    - Links das abas de navegação
    - Restauração de carrinho com ciclo já finalizado
    - Criação de novo carrinho quando o existente já estava finalizado
    - Data e hora incorretas na tabela de carrinhos
    - Erro fatal por argumentos insuficientes em `Recovery_Handler::send_follow_up_message_callback`
    - Notificação de atualização disponível não era ocultada após atualizar o plugin
    - Cancelamento de eventos agendados ao receber pedido e ao recuperar carrinho
    - Exclusão do post `fcrc-cron-event` ao cancelar evento agendado
    - Validações adicionais para função `WC()` e método de sessão
* Recurso adicionado: Agendador de tarefas PHP-Cron (opção para escolher entre WP-Cron e PHP-Cron nas configurações)
* Recurso adicionado: Sistema de Webhooks para envio de dados de eventos
* Recurso adicionado: Comandos WP-CLI (`wp fcrc scheduler` com flag `--print-cron`, `wp fcrc cart-list`)
* Recurso adicionado: Fila de processamentos (Queue Table) com ações
* Recurso adicionado: Limpeza de eventos vencidos e órfãos da fila de processamentos (botão manual e cron diário, com tolerância de 1 hora)
* Recurso adicionado: Limpeza de duplicatas do WP-Cron
* Recurso adicionado: Enviar mensagem de teste de follow up por evento, com campo de telefone de teste nas configurações do Joinotify
* Recurso adicionado: Atualização automática da tabela de carrinhos via AJAX quando um novo carrinho é criado
* Recurso adicionado: Bloquear envio de follow ups após X dias da compra
* Recurso adicionado: Cancelar follow ups após compra tardia
* Recurso adicionado: Intervalo de tempo entre envios de follow up
* Recurso adicionado: Prevenir notificação para clientes vinculados ao telefone ou e-mail do carrinho
* Recurso adicionado: Bloquear follow ups após compra
* Recurso adicionado: Verificação de envio bem-sucedido de mensagem do WhatsApp
* Recurso adicionado: Definir automaticamente o primeiro remetente do Joinotify quando vazio
* Recurso adicionado: Comando WP-CLI `wp fcrc cart-list` para listar carrinhos por status com limite

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

Versão 1.0.0 (06/03/2025)
* Versão inicial