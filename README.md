# HelpDesk 🛠️

O **HelpDesk** é uma plataforma de suporte técnico e gerenciamento de chamados desenvolvida como projeto acadêmico para o curso de **Análise e Desenvolvimento de Sistemas (ADS)** no **UniSENAI**. O sistema foca em segurança, comunicação automatizada e uma interface organizada para o usuário.

---

## 📂 Estrutura do Projeto

Abaixo está a hierarquia de diretórios e arquivos do repositório, organizada para facilitar a manutenção e escalabilidade:

### 1. Raiz do Projeto
Contém os arquivos fundamentais para a inicialização e acesso ao sistema:
*   `index`: Página inicial e portal de entrada para login.
*   `autenticar`: Lógica de validação de credenciais de acesso.
*   `conexao`: Configurações de ponte com o banco de dados.

### 2. Assets (Recursos Estáticos)
*   `📁 css/`: Diretório de estilização, contendo arquivos específicos como o do `login`.
*   `📁 js/`: Scripts de front-end para interatividade:
    *   `flatpickr-config`: Configurações para seletores de data/calendário.
    *   `mensagens`: Lógica de exibição de alertas e notificações ao usuário.
    *   `scripts`: Funções gerais de JavaScript para a interface.

### 3. Painel Administrativo
*   `📁 painel/`: Área restrita do sistema pós-autenticação:
    *   `index`: Dashboard principal para gestão de chamados.
    *   `logout`: Encerramento seguro da sessão do usuário.
    *   `📁 funcoes/`: Scripts de processamento interno, incluindo `crypto` (segurança/criptografia), `email` (disparo de mensagens) e `teste_email` (diagnóstico).

### 4. Gestão de Comunicação e Automação
*   `📁 phpmailer/`: Integração da biblioteca PHPMailer para envio profissional de e-mails:
    *   `📁 src/`: Classes essenciais como `PHPMailer`, `SMTP`, `OAuth` e `Exception`.
    *   `📁 language/`: Suporte a traduções de erros em múltiplos idiomas.
    *   `README`, `SECURITY` e `VERSION`: Documentação técnica e controle de versão da biblioteca.
*   `📁 scripts/`: Rotinas de backend e manutenção de segurança:
    *   `limpar_tokens_expirados`: Manutenção periódica da segurança do banco de dados.
    *   `recuperar_senha_enviar`: Fluxo de solicitação de recuperação de conta.
    *   `resetar_senha`: Lógica para alteração final de credenciais.

---

## 🛠️ Tecnologias Utilizadas

*   **Linguagem Principal**: PHP.
*   **Estilização**: CSS e práticas modernas de UX.
*   **Banco de Dados**: MySQL (gerenciado via `conexao`).
*   **Integrações**: PHPMailer para comunicação via SMTP/OAuth.
*   **Ferramentas**: Flatpickr para interface de calendário.

---

## 🎓 Informações do Projeto

*   **Desenvolvedora**: Ana Paula dos Reis.
*   **Curso**: Análise e Desenvolvimento de Sistemas (ADS).
*   **Instituição**: UniSENAI.
*   **Ano de Desenvolvimento**: 2026.

---

### 📄 Licença
Consulte o arquivo `LICENSE` localizado na pasta `phpmailer/` para detalhes sobre os termos de uso das bibliotecas de terceiros integradas a este software.

