<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lang Package
 *
 * @package    tool_sentry
 * @author     Esdras Caleb <esdrascaleb@gmail.com>
 * @copyright  2023 Esdras Caleb
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['activate'] = "Ativar Plugin Sentry";
$string['activate_desc'] = "Ativa o Sentry para enviar informações ao DSN configurado";
$string['always'] = "O SDK sempre capturará o corpo da requisição enquanto o Sentry puder interpretá-lo.";
$string['attach_stacktrace'] = "Anexar Rastreamento de Pilha";
$string['attach_stacktrace_desc'] = "Quando habilitado, os rastreamentos de pilha são automaticamente anexados a todas as mensagens registradas. Rastreamentos de pilha são sempre anexados a exceções; porém, com esta opção, eles também são enviados com mensagens. Por exemplo, isso faz com que rastreamentos de pilha apareçam ao lado de todas as mensagens de log.";
$string['dsn'] = "DSN do Servidor Sentry";
$string['dsn_desc'] = "Endereço do servidor Sentry com token de autenticação. Ex: https://USERCODE@CLIENTCODE.ingest.sentry.io/CLIENTCODE";
$string['enable_tracing'] = "Habilitar Rastreamento";
$string['enable_tracing_desc'] = "Valor booleano; se verdadeiro, transações e dados de rastreamento serão gerados e capturados. Isso define traces-sample-rate para o valor padrão recomendado 1.0, caso não esteja definido. Note que traces-sample-rate e traces-sampler têm prioridade sobre esta opção.";
$string['environment'] = "Ambiente do Sentry";
$string['environment_desc'] = "Define o ambiente. Esta string é livre e por padrão está como 'production'. Um release pode ser associado a mais de um ambiente para separá-los na interface (ex.: staging vs production).";
$string['error_types'] = "Tipos de Erro";
$string['error_types_desc'] = "Define quais erros serão reportados. Aceita os mesmos valores que o parâmetro error_reporting do PHP. Por padrão, todos os tipos de erro são reportados (equivalente a E_ALL).";
$string['ignore_exceptions'] = "Ignorar Exceções";
$string['ignore_exceptions_desc'] = "Lista de nomes de classes correspondentes às exceções que não devem ser enviadas ao Sentry. Verifica se a classe fornecida é do tipo ou subtipo especificado.";
$string['ignore_transactions'] = "Ignorar Transações";
$string['ignore_transactions_desc'] = "Lista de strings que correspondem a nomes de transações que não devem ser enviadas ao Sentry.";
$string['in_app_exclude'] = "Excluir do App";
$string['in_app_exclude_desc'] = "Lista de prefixos de nomes de módulos que não pertencem ao app, mas a pacotes de terceiros. Módulos considerados fora do app serão ocultados nos rastreamentos de pilha por padrão.";
$string['in_app_include'] = "Incluir no App";
$string['in_app_include_desc'] = "Lista de prefixos de nomes de módulos que pertencem ao app. Esta opção tem prioridade sobre in-app-exclude.";
$string['javascriptloader'] = "Carregador Javascript";
$string['javascriptloader_desc'] = "Insira apenas a URL do src da tag script encontrada em Configurações > Projetos > (selecionar projeto) > Client Keys (DSN) > Configurar > Carregador JavaScript. Exemplo: https://js.sentry-cdn.com/USERCODE.min.js";
$string['max_breadcrumbs'] = "Máximo de Breadcrumbs";
$string['max_breadcrumbs_desc'] = "Controla o número total de breadcrumbs que devem ser capturados. O padrão é 100, mas pode ser ajustado. Lembre-se que o Sentry possui limite máximo de payload e eventos que excederem esse limite serão descartados.";
$string['max_request_body_size'] = "Tamanho Máximo do Corpo da Requisição";
$string['max_request_body_size_desc'] = "Controla se integrações devem capturar corpos de requisições HTTP. O servidor Sentry limita o tamanho do corpo da requisição e sempre aplica esse limite, independentemente da configuração aqui.";
$string['max_value_length'] = "Comprimento Máximo do Valor";
$string['max_value_length_desc'] = "Número de caracteres após o qual valores de texto no payload do evento serão truncados (padrão 1024).";
$string['medium'] = "Requisições médias e pequenas serão capturadas (tipicamente 10KB)";
$string['never'] = "Corpos de requisição nunca serão enviados.";
$string['options'] = "Opções";
$string['options_desc'] = "São necessárias para o funcionamento";
$string['pluginname'] = "Ferramenta de relatório Sentry";
$string['pluginsettings'] = "Configuração do Sentry";
$string['prepend_active'] = "Iniciado antes do Moodle (auto_prepend_file)";
$string['prepend_active_desc'] = "O Sentry foi iniciado por admin/tool/sentry/prepend.php, com as opções das variáveis de ambiente SENTRY_*. As opções de PHP abaixo são ignoradas enquanto SENTRY_DSN estiver definida; só o carregador Javascript continua sendo usado.";
$string['privacy:metadata'] = "O plugin não armazena dados pessoais. Contudo, envia o IP do usuário que gerou um erro para o servidor Sentry configurado.";
$string['profiles_sample_rate'] = "Taxa de Amostragem de Perfil";
$string['profiles_sample_rate_desc'] = "Para o Profiling funcionar, é preciso primeiro habilitar o rastreamento do Sentry via traces_sample_rate (como no exemplo acima). Consulte a documentação de rastreamento para configurar a amostragem. Se definir 1.0, todas as transações serão capturadas.";
$string['release'] = "Release do Sentry";
$string['release_desc'] = "Define o release. Se não definido, o SDK tentará configurar automaticamente, mas é melhor definir manualmente para garantir que esteja sincronizado com suas integrações de deploy.";
$string['sample_rate'] = "Taxa de Amostragem";
$string['sample_rate_desc'] = "Configura a taxa de amostragem de eventos de erro, de 0.0 a 1.0. Padrão 1.0 (100% dos eventos). Ex.: 0.1 envia apenas 10% dos eventos, escolhidos aleatoriamente.";
$string['send_default_pii'] = "Enviar PII Padrão";
$string['send_default_pii_desc'] = "Se ativado, certas informações pessoalmente identificáveis (PII) são enviadas por integrações ativas. Por padrão, nenhum dado é enviado.";
$string['sentry_options'] = "Opções do Sentry";
$string['sentry_options_desc'] = "Altera como as opções são enviadas ao Sentry";
$string['server_name'] = "Nome do Servidor";
$string['server_name_desc'] = "Esta opção pode definir o \"nome do servidor\". Quando fornecido, o nome é enviado e persistido no evento. Em muitas integrações, corresponde ao hostname da máquina, mesmo que não seja realmente um servidor.";
$string['small'] = "Apenas corpos de requisição pequenos serão capturados (tipicamente 4KB)";
$string['test_conn'] = "Testar Conexões";
$string['test_exception'] = "Enviar Exceção";
$string['traces_sample_rate'] = "Taxa de Amostragem de Rastreamentos";
$string['traces_sample_rate_desc'] = "Número entre 0 e 1, controlando a probabilidade de envio de uma transação ao Sentry (0 = 0%, 1 = 100%). Aplica-se a todas as transações do app. Deve ser definido junto com traces_sampler para habilitar rastreamento.";
