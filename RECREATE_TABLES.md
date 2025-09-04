# 🔧 Guia para Recriar Tabelas de Reuniões

## ⚠️ IMPORTANTE
Seu amigo apagou as tabelas `reunioes` e `reuniao_participantes`. Este guia contém as instruções **EXATAS** para recriá-las baseado na análise completa do código atual.

## 📋 Banco de Dados
- **Nome do banco**: `cadastro_empresas` (conforme configurado em `config/database.php`)
- **Charset**: `utf8mb4_unicode_ci`

## 🗂️ Tabelas Identificadas no Código

### 1. Tabela: `reunioes`
**Localização no código**: `classes/Meeting.php` linha 6
```php
private $table_name = "reunioes";
```

**Estrutura da tabela**:
```sql
CREATE TABLE `reunioes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `data_reuniao` DATE NOT NULL,
    `hora_inicio` TIME NOT NULL,
    `hora_fim` TIME NOT NULL,
    `assunto` VARCHAR(255) NOT NULL,
    `descricao` TEXT,
    `status` ENUM('agendada', 'em_andamento', 'concluida', 'cancelada') DEFAULT 'agendada',
    `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_data_reuniao` (`data_reuniao`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Campos utilizados no código**:
- `id` - Chave primária
- `data_reuniao` - Data da reunião (usado em `getMeetingStatus()`)
- `hora_inicio` - Hora de início (usado em `getMeetingStatus()`)
- `hora_fim` - Hora de fim (usado em `getMeetingStatus()`)
- `assunto` - Assunto da reunião (usado no modal e tooltip)
- `descricao` - Descrição da reunião (campo opcional)
- `status` - Status da reunião (calculado dinamicamente)
- `data_criacao` - Data de criação (usado no INSERT)
- `data_atualizacao` - Data de atualização (usado no UPDATE)

### 2. Tabela: `reuniao_participantes`
**Localização no código**: `classes/Meeting.php` linha 7
```php
private $participants_table = "reuniao_participantes";
```

**Estrutura da tabela**:
```sql
CREATE TABLE `reuniao_participantes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `reuniao_id` INT(11) NOT NULL,
    `id_usuario` INT(11) NOT NULL,
    `username_usuario` VARCHAR(100) NOT NULL,
    `status_participacao` ENUM('confirmado', 'pendente', 'recusado') DEFAULT 'confirmado',
    `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`reuniao_id`) REFERENCES `reunioes`(`id`) ON DELETE CASCADE,
    INDEX `idx_reuniao_id` (`reuniao_id`),
    INDEX `idx_id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Campos utilizados no código**:
- `id` - Chave primária
- `reuniao_id` - ID da reunião (FK para `reunioes.id`)
- `id_usuario` - ID do usuário participante
- `username_usuario` - Nome/username do participante (usado no modal)
- `status_participacao` - Status do participante (sempre 'confirmado' no código atual)
- `data_criacao` - Data de criação (usado no INSERT)

## 🚀 Script SQL Completo

Execute este script no banco `cadastro_empresas`:

```sql
-- Usar o banco correto
USE cadastro_empresas;

-- Criar tabela de reuniões
CREATE TABLE `reunioes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `data_reuniao` DATE NOT NULL,
    `hora_inicio` TIME NOT NULL,
    `hora_fim` TIME NOT NULL,
    `assunto` VARCHAR(255) NOT NULL,
    `descricao` TEXT,
    `status` ENUM('agendada', 'em_andamento', 'concluida', 'cancelada') DEFAULT 'agendada',
    `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `data_atualizacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_data_reuniao` (`data_reuniao`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de participantes
CREATE TABLE `reuniao_participantes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `reuniao_id` INT(11) NOT NULL,
    `id_usuario` INT(11) NOT NULL,
    `username_usuario` VARCHAR(100) NOT NULL,
    `status_participacao` ENUM('confirmado', 'pendente', 'recusado') DEFAULT 'confirmado',
    `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`reuniao_id`) REFERENCES `reunioes`(`id`) ON DELETE CASCADE,
    INDEX `idx_reuniao_id` (`reuniao_id`),
    INDEX `idx_id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## ✅ Verificação

Após executar o script, verifique se as tabelas foram criadas:

```sql
-- Verificar se as tabelas existem
SHOW TABLES LIKE 'reunioes';
SHOW TABLES LIKE 'reuniao_participantes';

-- Verificar estrutura das tabelas
DESCRIBE reunioes;
DESCRIBE reuniao_participantes;
```

## 📝 Arquivos que Precisam das Tabelas

### APIs que usam `reunioes`:
- `api/get_meetings.php` - Lista todas as reuniões
- `api/create_meeting.php` - Cria nova reunião
- `api/update_meeting.php` - Atualiza reunião existente
- `api/delete_meeting.php` - Exclui reunião

### APIs que usam `reuniao_participantes`:
- `api/get_meeting_participants.php` - Lista participantes
- `api/create_meeting.php` - Adiciona participantes
- `api/update_meeting.php` - Atualiza participantes

### Classes que dependem das tabelas:
- `classes/Meeting.php` - Classe principal de reuniões

### Dashboards que exibem os dados:
- `dashboard/hr_manager.php` - Calendário com bolinhas coloridas
- `dashboard/admin.php` - Visualização administrativa

## 🔍 Status das Reuniões

O sistema calcula o status dinamicamente baseado no horário:
- **agendada** - Reunião futura (bolinha laranja)
- **em_andamento** - Reunião acontecendo agora (bolinha verde)
- **concluida** - Reunião finalizada há mais de 5 minutos (bolinha vermelha)
- **cancelada** - Reunião cancelada manualmente

## ⚡ Após Criar as Tabelas

1. Acesse `http://localhost:4000/dashboard/hr_manager.php`
2. As bolinhas coloridas devem aparecer no calendário
3. Teste criar uma nova reunião
4. Verifique se o modal de edição funciona

## 🆘 Problemas Comuns

### Erro "Table doesn't exist"
- Verifique se está no banco `cadastro_empresas`
- Confirme que executou o script SQL completo

### Bolinhas não aparecem
- Verifique se a API `get_meetings.php` retorna dados
- Abra o console do navegador para ver erros JavaScript

### Erro de Foreign Key
- Certifique-se de criar `reunioes` ANTES de `reuniao_participantes`
- A tabela `users` deve existir no banco

## 🎯 Dados de Teste

Para testar o sistema, execute este script para inserir algumas reuniões de exemplo:

```sql
-- Inserir reuniões de teste
INSERT INTO `reunioes` (`data_reuniao`, `hora_inicio`, `hora_fim`, `assunto`, `descricao`, `status`) VALUES
-- Reunião acontecendo agora (verde)
(CURDATE(), DATE_SUB(NOW(), INTERVAL 30 MINUTE), DATE_ADD(NOW(), INTERVAL 30 MINUTE), 'Reunião de Alinhamento', 'Reunião semanal da equipe', 'agendada'),

-- Reunião finalizada (vermelha) 
(CURDATE(), '08:00:00', '09:00:00', 'Reunião Matinal', 'Briefing do dia', 'agendada'),

-- Reunião futura hoje (laranja)
(CURDATE(), '16:00:00', '17:00:00', 'Reunião de Fechamento', 'Revisão das atividades do dia', 'agendada'),

-- Reunião amanhã (laranja)
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '11:00:00', 'Reunião de Planejamento', 'Planejamento semanal', 'agendada'),

-- Reunião ontem (vermelha)
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '14:00:00', '15:00:00', 'Reunião de Review', 'Review do projeto', 'agendada');

-- Inserir alguns participantes (opcional)
-- Substitua os IDs pelos IDs reais dos usuários da tabela users
INSERT INTO `reuniao_participantes` (`reuniao_id`, `id_usuario`, `username_usuario`, `status_participacao`) VALUES
(1, 1, 'Admin', 'confirmado'),
(2, 1, 'Admin', 'confirmado'),
(3, 1, 'Admin', 'confirmado'),
(4, 1, 'Admin', 'confirmado'),
(5, 1, 'Admin', 'confirmado');
```

### 🔍 Verificar Dados Inseridos

```sql
-- Ver todas as reuniões
SELECT * FROM reunioes ORDER BY data_reuniao, hora_inicio;

-- Ver participantes
SELECT * FROM reuniao_participantes;
```

---
**Gerado automaticamente pela análise do código em:** `<?php echo date('d/m/Y H:i:s'); ?>`
