# 🎫 HelpDesk — Sistema de Suporte e Chamados

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![PHPMailer](https://img.shields.io/badge/PHPMailer-SMTP-red?style=for-the-badge)
![License](https://img.shields.io/badge/Licença-MIT-green?style=for-the-badge)

> Sistema completo de abertura, gestão e acompanhamento de chamados de suporte técnico, com painel administrativo, portal do cliente, notificações por e-mail e WhatsApp, e integração com IA para geração de mensagens.

---

## ✨ Funcionalidades

- **Abertura e gestão de chamados** com protocolo único gerado automaticamente (ex: `CH20260224-BEF7DD`)
- **Painel Administrativo** com dashboard, filtros avançados e relatórios em PDF
- **Portal do Cliente** — acesso separado por CPF/CNPJ ou telefone para acompanhar chamados
- **Sistema de Respostas e Anexos** em cada chamado (suporte a PDF, imagens, ZIP e outros)
- **Notificações por E-mail** via SMTP (PHPMailer) para clientes e responsáveis
- **Integração com WhatsApp** (APIs: MenuIA e Meta/WhatsApp Business)
- **Geração de mensagens com IA** (modelo configurável via painel)
- **Controle de Permissões** por usuário: menus, ações (criar/editar/excluir) e setores
- **Cadastro de Clientes** com importação via XLSX e busca automática por CPF/CNPJ
- **Gestão de Usuários** com níveis (Administrador / Atendente / Comum)
- **Log de Auditoria** completo (login, logout, inserções, edições, exclusões) com relatório em PDF
- **Backup do banco de dados** gerado diretamente pelo painel
- **Personalização visual** — cores primária/secundária, logo e ícone configuráveis
- **Proteção CSRF**, senhas com `password_hash/verify`, SQL via PDO com prepared statements

---

## 🛠️ Tecnologias

| Camada | Tecnologia |
|---|---|
| Linguagem back-end | PHP 8.2 |
| Banco de Dados | MySQL / MariaDB 10.4+ |
| Front-end | HTML5, CSS3, JavaScript (ES6+) |
| Framework CSS | Bootstrap 5.3 |
| Ícones | Bootstrap Icons 1.11 |
| Envio de E-mail | PHPMailer (SMTP) |
| Exportação PDF | pdfmake (client-side) |
| Tabelas interativas | DataTables |
| Integração WhatsApp | API MenuIA / Meta (WhatsApp Business) |
| Geração de texto IA | API configurável via painel |
| Importação de dados | XLSX (via JavaScript) |

---

## 📋 Pré-requisitos

Antes de iniciar, certifique-se de ter instalado:

- **PHP** >= 8.0 (recomendado 8.2) com as extensões: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`
- **MySQL** >= 5.7 ou **MariaDB** >= 10.4
- **Apache** ou **Nginx** com `mod_rewrite` habilitado
- **Composer** (opcional, caso queira atualizar o PHPMailer)
- Acesso a um servidor **SMTP** para envio de e-mails

---

## 🚀 Instalação

### 1. Clone ou copie os arquivos

```bash
# Via Git
git clone https://github.com/AnaPauladosReis/helpdesk.git

# Ou extraia o .zip na pasta do servidor web
# Exemplo no XAMPP:
# C:\xampp\htdocs\helpdesk\
```

### 2. Crie o banco de dados

```sql
CREATE DATABASE helpdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

### 3. Importe o dump SQL

```bash
mysql -u root -p helpdesk < helpdesk.sql
```

> O arquivo `helpdesk.sql` está na raiz do projeto e já contém a estrutura completa de tabelas e dados iniciais.

### 4. Configure a conexão e o sistema

Edite o arquivo `conexao.php` na raiz do projeto:

```php
// Banco de dados
$servidor = 'localhost';
$usuario  = 'root';
$senha    = 'sua_senha_aqui';
$banco    = 'helpdesk';

// SMTP (e-mail)
$smtp_host      = 'mail.seudominio.com.br';
$smtp_senha     = 'senha_smtp';
$smtp_porta     = 587;
$smtp_seguranca = 'tls';
```

### 5. Configure as permissões de pasta

```bash
# Linux/Mac — permissão de escrita para uploads e backups
chmod -R 775 helpdesk/uploads/
chmod -R 775 helpdesk/painel/backups/
```

### 6. Acesse o sistema

| URL | Descrição |
|---|---|
| `http://localhost/helpdesk/` | Tela de login do Painel Administrativo |
| `http://localhost/helpdesk/acesso` | Portal de acesso do Cliente |


## 📁 Estrutura de Pastas

```
helpdesk/
│
├── conexao.php                  # Configurações do sistema, BD e SMTP
├── index.php                    # Tela de login (painel admin)
├── autenticar.php               # Processamento do login administrativo
├── acesso.php                   # Tela de login do cliente
├── autenticar_cliente.php       # Processamento do login do cliente
├── chamado.php                  # Visualização pública de chamado por protocolo
├── helpdesk.sql                 # Dump completo do banco de dados
│
├── painel/                      # Área administrativa (logada)
│   ├── index.php                # Roteador principal do painel
│   ├── verificar.php            # Verificação de sessão
│   ├── logout.php               # Encerramento de sessão
│   ├── ajax/                    # Endpoints AJAX (CRUD)
│   │   ├── abertura/            # Chamados: salvar, listar, respostas, anexos
│   │   ├── clientes/            # Clientes: salvar, excluir, importar XLSX
│   │   ├── usuarios/            # Usuários: salvar, excluir
│   │   ├── cargos/              # Cargos: salvar, excluir
│   │   ├── setores/             # Setores: salvar, excluir
│   │   ├── status_abertura/     # Status de chamado: salvar, excluir
│   │   └── backup/              # Geração de backup SQL
│   ├── apis/                    # Integrações externas (WhatsApp, IA, e-mail)
│   ├── config/
│   │   └── menu.php             # Configuração do sidebar e menus
│   ├── includes/                # Partials: head, sidebar, topbar, permissões, logs
│   ├── paginas/                 # Páginas renderizadas (dashboard, chamados, etc.)
│   ├── relatorios/              # Geração de PDFs (chamados, logs)
│   └── backups/                 # Arquivos .sql gerados pelo sistema
│
├── painel_cliente/              # Portal do cliente (logado)
│   ├── index.php                # Roteador do portal
│   ├── verificar.php            # Verificação de sessão do cliente
│   ├── config/menu.php          # Menu do portal do cliente
│   └── assets/                  # DataTables, pdfmake e demais libs
│
├── phpmailer/                   # Biblioteca PHPMailer para SMTP
└── uploads/                     # Arquivos enviados (logos, fotos, anexos)
    └── arquivos/                # Anexos dos chamados
```

---

## 💻 Como Usar

### Fluxo básico — Administrador

1. Acesse `http://localhost/helpdesk/` e faça login com as credenciais de administrador.
2. No **Dashboard**, visualize os indicadores de chamados abertos, pendentes e resolvidos.
3. Acesse **Abertura de Chamados** → clique em **Novo Chamado** para criar um ticket.
4. Preencha cliente, assunto, descrição, prioridade, setor e responsável.
5. O sistema gera automaticamente um **protocolo único** e envia e-mail de notificação.
6. Adicione **respostas** e **anexos** ao chamado diretamente pelo painel.
7. Em **Relatórios**, exporte os dados filtrados em **PDF**.
8. Em **Backup**, gere um dump `.sql` do banco com um clique.

### Fluxo básico — Cliente

1. Acesse `http://localhost/helpdesk/acesso` e faça login com CPF/CNPJ ou telefone.
2. Visualize seus chamados abertos, em andamento e encerrados.
3. Acompanhe o histórico de respostas e faça download de anexos.
4. Consulte o chamado diretamente via protocolo em:
   ```
   http://localhost/helpdesk/chamado?protocolo=CH20260224-BEF7DD
   ```

### Exemplo de criação de chamado via painel (AJAX)

```http
POST /helpdesk/painel/ajax/abertura/salvar.php

Body (form-data):
  cliente_id     = 12
  assunto        = "Problema no acesso ao sistema"
  descricao      = "O usuário não consegue efetuar login desde ontem."
  prioridade     = "Alta"
  status_id      = 1
  setor_id       = 3
  responsavel_id = 5

Response:
{
  "ok": true,
  "msg": "Chamado aberto com sucesso!",
  "id": 36,
  "protocolo": "CH20260514-A1B2C3"
}
```

---

## 🔐 Segurança

O sistema adota as seguintes práticas de segurança:

- Proteção contra **SQL Injection** via PDO com prepared statements
- Proteção **CSRF** em todos os formulários de login
- Senhas armazenadas com **`password_hash` (bcrypt)**
- **Regeneração de sessão** após autenticação (mitiga session fixation)
- Validação e sanitização de todas as entradas do usuário
- Upload de arquivos com **whitelist de extensões** e nome único gerado no servidor
- Controle de acesso por **nível de usuário** e **permissões granulares por menu**

---

## 👤 Créditos

Desenvolvido por **[Ana Paula dos Reis]**
