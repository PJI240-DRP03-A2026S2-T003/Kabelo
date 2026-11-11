# Kabelo

Sistema web de agendamento do **Salão Zelia Andrade**, desenvolvido no **Projeto Integrador II** da **UNIVESP**.

O Kabelo substitui a agenda em papel do salão: a equipe cadastra clientes, profissionais e serviços, marca horários sem risco de conflito e acompanha a agenda pelo painel. Na véspera, o sistema envia a cada cliente um lembrete pelo WhatsApp.

## Funcionalidades

- **Cadastros**: clientes, profissionais e serviços, com duração e preço.
- **Horários livres de 15 em 15 minutos**: ao agendar, o sistema mostra só os horários disponíveis do profissional, levando em conta a duração do serviço, os outros agendamentos e os bloqueios.
- **Bloqueios de agenda**: folgas, almoço e compromissos, pontuais ou recorrentes (diários ou semanais, com data final opcional).
- **Dashboard**: indicadores do dia, lista dos próximos agendamentos e calendário semanal.
- **Lembrete por WhatsApp**: um dia antes do horário, a cliente recebe uma mensagem com serviço, profissional, data e hora. O lembrete só é enviado para quem autorizou no cadastro.

## Tecnologias

- PHP 8.3 e Laravel 13
- Filament 3 (painel administrativo)
- PostgreSQL (Supabase) em produção e SQLite no desenvolvimento local
- Docker
- FullCalendar (calendário do dashboard)
- WhatsApp Cloud API (lembretes)

## Como rodar

Pré-requisitos: PHP 8.3, Composer e Node.js.

```bash
git clone https://github.com/PJI240-DRP03-A2026S2-T003/Kabelo.git
cd Kabelo

# instala as dependências, cria o .env e gera a chave da aplicação
composer run setup

# cria as tabelas no banco configurado no .env
php artisan migrate

# cria o usuário de acesso ao painel
php artisan make:filament-user

# sobe o servidor
php artisan serve
```

Depois, acesse **http://localhost:8000/admin** e entre com o usuário criado.

### Lembretes por WhatsApp

Preencha no `.env` as variáveis `WHATSAPP_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID`, `WHATSAPP_TEMPLATE_LEMBRETE` e `WHATSAPP_VERIFY_TOKEN`. Para ver quais mensagens seriam enviadas, sem enviar nada:

```bash
php artisan agendamentos:lembretes --simular
```

Em produção, o Vercel Cron chama a rota `/cron/lembretes`.

## Integrantes

- Gabriel Alves Gazale
- Flávio Vinícius Barbosa Almeida
- Felipe Rosa de Oliveira Godoy
