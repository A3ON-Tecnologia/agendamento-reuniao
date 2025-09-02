# Sistema de Agendamento de Reuniões - AgendaRH

Sistema completo para agendamento de reuniões com o RH, desenvolvido em PHP, MySQL, HTML e Tailwind CSS.

## 🚀 Características

- **Interface moderna e responsiva** com Tailwind CSS
- **Sistema de autenticação** com 3 níveis de permissão
- **Calendário interativo** para visualização e agendamento
- **Configuração flexível** de horários disponíveis
- **Histórico completo** de reuniões
- **Dashboard personalizado** para cada tipo de usuário

## 👥 Tipos de Usuário

### 🏢 Gestor de RH
- Ver todas as reuniões agendadas com detalhes completos
- Configurar horários disponíveis por dia da semana
- Acesso completo às descrições das reuniões
- Gerenciar status das reuniões (concluir/cancelar)
- Histórico completo com nome dos participantes

### 🛡️ Admin (Chefes da Empresa)
- Ver quem marcou reuniões (sem descrições)
- Agendar próprias reuniões
- Visão geral do sistema e estatísticas
- Gerenciar usuários do sistema
- Controle de quem está fazendo reuniões

### 👤 Usuário Comum
- Ver calendário com horários disponíveis
- Agendar reuniões com descrição
- Ver apenas suas próprias reuniões
- Não pode ver reuniões de outros usuários

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP 7.4+
- **Banco de Dados:** MySQL 5.7+
- **Frontend:** HTML5, JavaScript (ES6+)
- **Estilização:** Tailwind CSS
- **Ícones:** Font Awesome 6.0

## 📋 Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx) ou XAMPP/WAMP
- Navegador web moderno

## 🔧 Instalação

### 1. Clone ou baixe o projeto
```bash
git clone [url-do-repositorio]
cd agendamento-walter
```

### 2. Configure o banco de dados
1. Crie um banco de dados MySQL chamado `agendamento_reunioes`
2. Execute o script SQL localizado em `database/schema.sql`
3. Ajuste as configurações de conexão em `config/database.php`

```php
// config/database.php
private $host = 'localhost';
private $db_name = 'agendamento_reunioes';
private $username = 'seu_usuario';
private $password = 'sua_senha';
```

### 3. Configure o servidor web
- Aponte o documento root para a pasta do projeto
- Certifique-se de que o PHP está habilitado
- Verifique se a extensão PDO MySQL está ativa

### 4. Acesse o sistema
Abra o navegador e acesse: `http://localhost/agendamento-walter`

## 👤 Contas de Teste

O sistema vem com contas pré-configuradas para teste:

| Tipo | Email | Senha | Descrição |
|------|-------|-------|-----------|
| Gestor RH | rh@empresa.com | password | Acesso completo ao sistema |
| Admin | admin@empresa.com | password | Visão administrativa |
| Usuário | usuario@empresa.com | password | Usuário comum |

## 📁 Estrutura do Projeto

```
agendamento-walter/
├── api/                    # Endpoints da API
│   ├── book_appointment.php
│   ├── get_available_slots.php
│   ├── save_availability.php
│   └── update_appointment_status.php
├── auth/                   # Sistema de autenticação
│   ├── login.php
│   ├── logout.php
│   └── register.php
├── classes/                # Classes do sistema
│   ├── Appointment.php
│   ├── AvailabilitySlot.php
│   └── User.php
├── config/                 # Configurações
│   └── database.php
├── dashboard/              # Dashboards por tipo de usuário
│   ├── admin.php
│   ├── hr_manager.php
│   └── user.php
├── database/               # Scripts do banco de dados
│   └── schema.sql
├── includes/               # Arquivos auxiliares
│   └── auth_check.php
├── index.php              # Página inicial
└── README.md              # Este arquivo
```

## 🔄 Fluxo de Uso

### Para Usuários Comuns:
1. Fazer login no sistema
2. Visualizar o calendário com horários disponíveis
3. Selecionar data e horário desejado
4. Adicionar descrição da reunião
5. Confirmar agendamento

### Para Gestores de RH:
1. Configurar horários disponíveis por dia da semana
2. Visualizar todas as reuniões agendadas
3. Gerenciar status das reuniões
4. Acessar histórico completo

### Para Admins:
1. Visualizar estatísticas do sistema
2. Ver quem está fazendo reuniões (sem descrições)
3. Agendar próprias reuniões
4. Gerenciar usuários

## 🔒 Segurança

- Senhas criptografadas com `password_hash()`
- Verificação de sessão em todas as páginas protegidas
- Controle de acesso baseado em roles
- Proteção contra SQL Injection com PDO
- Validação de dados no frontend e backend

## 🎨 Interface

- Design moderno e responsivo
- Cores e gradientes atraentes
- Ícones intuitivos
- Animações suaves
- Experiência de usuário otimizada

## 📱 Responsividade

O sistema é totalmente responsivo e funciona perfeitamente em:
- Desktop (1920px+)
- Tablet (768px - 1024px)
- Mobile (320px - 767px)

## 🐛 Solução de Problemas

### Erro de conexão com banco de dados
- Verifique as credenciais em `config/database.php`
- Certifique-se de que o MySQL está rodando
- Confirme se o banco `agendamento_reunioes` existe

### Página em branco
- Verifique se o PHP está habilitado
- Ative a exibição de erros no PHP
- Verifique os logs do servidor web

### Problemas de sessão
- Certifique-se de que as sessões PHP estão habilitadas
- Verifique permissões da pasta de sessões

## 🔄 Atualizações Futuras

Possíveis melhorias para versões futuras:
- Notificações por email
- Integração com calendários externos
- API REST completa
- Aplicativo mobile
- Relatórios em PDF
- Sistema de lembretes

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique este README
2. Consulte os comentários no código
3. Teste com as contas padrão

## 📄 Licença

Este projeto foi desenvolvido para uso interno da empresa. Todos os direitos reservados.

---

**Desenvolvido com ❤️ usando PHP, MySQL e Tailwind CSS**
