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

$string['activate'] = 'Ativar plugin Sentry';
$string['activate_desc'] = 'Ativa o Sentry para enviar informações ao DSN configurado.';
$string['always'] = 'O SDK sempre captura o corpo da requisição enquanto o Sentry puder interpretá-lo.';
$string['attach_stacktrace'] = 'Anexar rastreamento de pilha';
$string['attach_stacktrace_desc'] = 'Quando ativado, rastreamentos de pilha são automaticamente anexados a todas as mensagens registradas. Rastreamentos são sempre anexados a exceções, mas com esta opção, também são enviados junto com mensagens.';
$string['dsn'] = 'DSN do servidor Sentry';
$string['dsn_desc'] = 'Endereço do servidor Sentry com o token de autenticação.';
$string['environment'] = 'Ambiente Sentry';
$string['environment_desc'] = 'Define o ambiente. É uma string livre e o padrão é "production". Um release pode estar associado a mais de um ambiente para separá-los na interface.';
$string['enable_tracing'] = 'Habilitar rastreamento';
$string['enable_tracing_desc'] = 'Valor booleano que, se verdadeiro, gera e captura transações e dados de rastreamento. Define automaticamente traces_sample_rate para 1.0 se não definido.';
$string['error_types'] = 'Tipos de erro';
$string['error_types_desc'] = 'Define quais erros serão reportados. Usa os mesmos valores da configuração error_reporting do PHP. Por padrão, todos os erros são reportados (equivalente a E_ALL).';
$string['ignore_exceptions'] = 'Ignorar exceções';
$string['ignore_exceptions_desc'] = 'Lista de nomes de classes de exceção que não devem ser enviadas ao Sentry. Verifica se a classe fornecida é do tipo ou subtipo.';
$string['ignore_transactions'] = 'Ignorar transações';
$string['ignore_transactions_desc'] = 'Lista de strings que correspondem a nomes de transações que não devem ser enviadas ao Sentry.';
$string['in_app_exclude'] = 'Excluir do aplicativo';
$string['in_app_exclude_desc'] = 'Lista de prefixos de nomes de módulos que não pertencem ao aplicativo, mas sim a pacotes de terceiros. Esses módulos são ocultados dos rastreamentos de pilha.';
$string['in_app_include'] = 'Incluir no aplicativo';
$string['in_app_include_desc'] = 'Lista de prefixos de nomes de módulos que pertencem ao aplicativo. Esta opção tem prioridade sobre in_app_exclude.';
$string['javascriptloader'] = 'Carregador JavaScript';
$string['javascriptloader_desc'] = 'Informe apenas a URL src da tag script encontrada em Configurações > Projetos > (selecione projeto) > Chaves do Cliente (DSN) > Configurar > Carregador JavaScript. Exemplo: https://js.sentry-cdn.com/USERCODE.min.js';
$string['max_breadcrumbs'] = 'Máximo de Breadcrumbs';
$string['max_breadcrumbs_desc'] = 'Controla o total de breadcrumbs que devem ser capturados. O padrão é 100, mas pode ser alterado. Atenção ao tamanho máximo do payload do Sentry.';
$string['max_request_body_size'] = 'Tamanho máximo do corpo da requisição';
$string['max_request_body_size_desc'] = 'Controla se integrações devem capturar corpos de requisição HTTP. O servidor Sentry impõe limite máximo para o tamanho do corpo.';
$string['max_value_length'] = 'Tamanho máximo do valor';
$string['max_value_length_desc'] = 'Número de caracteres após o qual valores contendo texto no payload serão truncados (padrão 1024).';
$string['never'] = 'Corpos das requisições nunca são enviados.';
$string['options'] = 'Opções';
$string['options_desc'] = 'São necessárias para o funcionamento do plugin.';
$string['pluginname'] = 'Ferramenta de relatório Sentry';
$string['pluginsettings'] = 'Configurações do Sentry';
$string['privacy:metadata'] = 'O plugin não armazena dados pessoais. Contudo, envia o IP do usuário que teve um erro para o servidor Sentry configurado.';
$string['profiles_sample_rate'] = 'Taxa de amostragem para perfilamento';
$string['profiles_sample_rate_desc'] = 'Para usar o perfilamento, primeiro habilite o rastreamento do Sentry via traces_sample_rate. Exemplo: com taxa 1.0, todas as transações são capturadas.';
$string['release'] = 'Versão do Sentry';
$string['release_desc'] = 'Define a versão. Se não configurada, o SDK tentará identificar automaticamente, mas é recomendado configurar manualmente para garantir sincronização.';
$string['sample_rate'] = 'Taxa de amostragem';
$string['sample_rate_desc'] = 'Configura a taxa de amostragem para eventos de erro, entre 0.0 e 1.0. O padrão é 1.0 (100% dos eventos). Com 0.1, apenas 10% são enviados, selecionados aleatoriamente.';
