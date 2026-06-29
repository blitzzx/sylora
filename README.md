<div align="center">

<img src="public/assets/img/Logo-Sylora.png" alt="Sylora" width="200"/>

# ✦ SYLORA ✦
### *Ecos dos Deuses*

*Num arquipélago esquecido pelos deuses, a tua saga começa.*

<br/>

[![PHP](https://img.shields.io/badge/PHP-8.3-c9993a?style=for-the-badge&logo=php&logoColor=white&labelColor=1c1710)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-c9993a?style=for-the-badge&logo=mysql&logoColor=white&labelColor=1c1710)](https://mysql.com)
[![Apache](https://img.shields.io/badge/Apache-2.4-c9993a?style=for-the-badge&logo=apache&logoColor=white&labelColor=1c1710)](https://httpd.apache.org)
[![Railway](https://img.shields.io/badge/Deploy-Railway-c9993a?style=for-the-badge&logo=railway&logoColor=white&labelColor=1c1710)](https://railway.app)

[![Estado](https://img.shields.io/badge/Estado-Alpha%20Aberto-e8c46a?style=for-the-badge&labelColor=1c1710)](https://www.sylora.lol)
[![Idiomas](https://img.shields.io/badge/Idiomas-PT%20·%20EN%20·%20ES-e8c46a?style=for-the-badge&labelColor=1c1710)](https://www.sylora.lol)

<br/>

### 🌊 **[JOGAR AGORA → sylora.lol](https://www.sylora.lol)** 🌊

<br/>

</div>

---

<div align="center">

## ⚔ &nbsp; O Mundo de Sylora &nbsp; ⚔

</div>

**Sylora** é um RPG de aventura narrativa, ambientado numa Grécia Antiga sombria e corrompida. Exploras um arquipélago de ilhas perdidas, cada uma guardando segredos, monstros e memórias esquecidas. Avanças por capítulos, ganhas poder, forjas amizades e descobres o que os deuses esconderam.

> *"Os mapas mostram terras. Sylora mostra destinos."*

Cada herói parte da mesma costa. Nenhum chega ao fim igual ao que partiu.

**A jornada atravessa cinco ilhas:**

| Ato | Ilha | |
|:---:|---|---|
| **I** | Ilha de Thalassos | *O Despertar* |
| **II** | Ilha de Helion | *As Cinzas de Hyperion* |
| **III** | Zephyria | *O Véu dos Ventos* |
| **IV** | Tártaro Profundo | *O Submundo pela Memória* |
| **V** | Templo Celestial de Themis | *O Julgamento dos Deuses* |

---

<div align="center">

## 🗺 &nbsp; Funcionalidades &nbsp; 🗺

</div>

| &nbsp; | Módulo | Descrição |
|:---:|---|---|
| ⚔️ | **Sistema de Jogo** | Progressão por capítulos com HP, XP, dano e saves persistentes na cloud |
| 📖 | **Narrativa** | História ramificada com progresso individual por jogador |
| 🗺️ | **Mundo** | Arquipélago navegável com áreas e ilhas desbloqueáveis |
| 🏆 | **Comunidade** | Leaderboard global com pódio animado, tiers e pesquisa de jogadores |
| 👤 | **Perfil de Herói** | Avatar personalizado com crop, bio pública e página de perfil |
| 🤝 | **Amizades** | Pedidos, aceitação e gestão de aliados |
| 💬 | **Muralha** | Comentários nos perfis, com moderação automática em dois níveis |
| 🔐 | **Autenticação** | Login com "lembrar-me", verificação OTP por email e reset de password |
| 🌗 | **Temas** | Dark mode "templo" e light mode "pergaminho" |
| 🌍 | **i18n** | Interface completa em Português, English e Español, sem reload |

---

<div align="center">

## 🔥 &nbsp; Junta-te à Aventura &nbsp; 🔥

</div>

Sylora está nos seus **primeiros passos** e isso significa que a tua voz tem peso real.

O jogo está vivo em **[sylora.lol](https://www.sylora.lol)**. Cria uma conta, explora o mundo, joga os capítulos disponíveis e diz-nos o que sentiste. Cada bug que encontras, cada sugestão que partilhas, cada momento que te surpreendeu: tudo isso molda o que Sylora vai ser.

**Como podes ajudar:**

- 🎮 **Joga** — explora tudo o que está disponível e [transfere a demo](https://www.sylora.lol)
- 🐛 **Reporta bugs** — abre uma [Issue](../../issues) com o que encontraste
- 💡 **Sugere** — tens uma ideia para o mundo ou para o jogo? Partilha nas Issues
- ⭐ **Dá uma estrela** — ajuda outros aventureiros a encontrar Sylora

> *Este é o início. O que construímos a seguir depende de quem aparece.*

---

<div align="center">

## 🛡 &nbsp; Segurança &nbsp; 🛡

</div>

A fortaleza que protege o reino:

- **SQL Injection** — prepared statements em todas as queries, sem exceção
- **Passwords** — bcrypt com `PASSWORD_DEFAULT`
- **Sessões** — tokens selector/validator com rotação, deteção de roubo e revogação global
- **Cookies** — `HttpOnly` + `SameSite` + `Secure` em HTTPS
- **CSRF** — tokens validados com `hash_equals` em todos os formulários POST
- **Uploads** — validação MIME real (finfo), `getimagesize` e re-encoding GD dos avatares
- **Rate Limiting** — em logins, emails e ações sensíveis
- **Headers** — CSP, HSTS, X-Frame-Options, nosniff via `.htaccess`
- **Credenciais** — exclusivamente via variáveis de ambiente; zero segredos no código

---

<div align="center">

## ⚗ &nbsp; Stack Tecnológica &nbsp; ⚗

</div>

```
Backend        →  PHP 8.3 + MySQLi (prepared statements)
Base de Dados  →  MySQL 8.0
Servidor       →  Apache 2.4 (mod_rewrite, .htaccess)
Contentor      →  Docker (php:8.3-apache)
Deploy         →  Railway
Email          →  Resend API / SMTP (PHPMailer)
Frontend       →  PHP templates + CSS/JS vanilla (sem frameworks)
```

**Arquitetura:**

```
sylora/
├── app/
│   ├── Core/          # Auth, Database, Mailer, i18n, helpers
│   ├── Http/Api/      # Endpoints JSON (saves, amigos, comentários…)
│   ├── Repositories/  # Acesso a dados
│   └── Services/      # Lógica de negócio + moderação
├── public/            # Docroot (páginas, CSS, JS, assets, APIs)
├── resources/
│   ├── lang/          # Traduções PT · EN · ES
│   └── views/         # Templates de páginas e partials
├── database/          # Schema da base de dados
└── scripts/           # Seeds de teste (NUNCA correr em produção)
```

---

<div align="center">

## 🕯 &nbsp; Instalação Local &nbsp; 🕯

</div>

**Pré-requisito:** [Docker](https://www.docker.com/) instalado.

```bash
# 1. Entra na pasta do projeto
cd sylora

# 2. Copia e preenche as variáveis de ambiente
cp .env.example .env

# 3. Sobe os containers
docker compose up -d

# 4. Importa o schema da base de dados
# Acede a http://localhost:8081 (phpMyAdmin) e importa database/schema.sql
```

Aventura disponível em **http://localhost:8080** ✦

> ⚠️ Os scripts em `scripts/` criam contas de teste com password conhecida.
> São apenas para load testing local — **nunca** os executes em produção.

---

<div align="center">

## 🏛 &nbsp; Os Artífices &nbsp; 🏛

Forjado por **Márcio Sousa** e **Samuel Meixieira**

<br/>

*Forjado com PHP, MySQL e um sopro de magia antiga.*

**✦ &nbsp; [sylora.lol](https://www.sylora.lol) &nbsp; ✦**

<br/>

</div>
