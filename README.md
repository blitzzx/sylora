<div align="center">

<img src="assets/img/Logo-Sylora.png" alt="Sylora" width="180"/>

<br/>

```
  █████  ██    ██ ██       ██████  ██████   █████
 ██       ██    ██ ██      ██    ██ ██   ██ ██   ██
  █████    ██████  ██      ██    ██ ██████  ███████
      ██     ██    ██      ██    ██ ██   ██ ██   ██
 ██████      ██    ███████  ██████  ██   ██ ██   ██
```

### ✦ *Ecos dos Deuses* ✦

*Num arquipélago esquecido pelos deuses, a tua saga começa.*

<br/>

[![PHP](https://img.shields.io/badge/PHP-8.3-7C3AED?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4F46E5?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Apache](https://img.shields.io/badge/Apache-2.4-6D28D9?style=for-the-badge&logo=apache&logoColor=white)](https://httpd.apache.org)
[![Railway](https://img.shields.io/badge/Deploy-Railway-4F46E5?style=for-the-badge&logo=railway&logoColor=white)](https://railway.app)
[![Status](https://img.shields.io/badge/Estado-Alpha%20Aberto-e8c46a?style=for-the-badge)](https://www.sylora.lol)

<br/>

**[🌊 Jogar Agora](https://www.sylora.lol)**

<br/>

</div>

---

<div align="center">

## ⚔ &nbsp; O Mundo de Sylora &nbsp; ⚔

</div>

**Sylora** é um RPG de aventura narrativa baseado na web, ambientado numa Grécia Antiga sombria e corrompida. Exploras um arquipélago de ilhas perdidas, cada uma guardando segredos, monstros e memórias esquecidas. Avanças por capítulos, ganhas poder, forjas amizades e descobres o que os deuses esconderam.

> *"Os mapas mostram terras. Sylora mostra destinos."*

Cada herói parte da mesma costa. Nenhum chega ao fim igual ao que partiu.

---

<div align="center">

## 🗺 &nbsp; Funcionalidades &nbsp; 🗺

</div>

| &nbsp; | Módulo | Descrição |
|:---:|---|---|
| ⚔️ | **Sistema de Jogo** | Progressão por capítulos com HP, XP, dano e saves persistentes |
| 📖 | **Narrativa** | História ramificada com progresso individual por jogador |
| 🗺️ | **Mundo** | Arquipélago navegável com áreas e ilhas desbloqueáveis |
| 👤 | **Perfil de Herói** | Avatar personalizado, bio pública e página de perfil |
| 🤝 | **Amizades** | Pedidos, aceitação e gestão de aliados |
| 💬 | **Muralha** | Comentários nos perfis de outros aventureiros |
| 🔐 | **Autenticação** | Login seguro com "lembrar-me", OTP de verificação e reset de password |
| 🔍 | **Pesquisa** | Encontra outros aventureiros pelo nome |

---

<div align="center">

## 🔥 &nbsp; Junta-te à Aventura &nbsp; 🔥

</div>

Sylora está nos seus **primeiros passos** — e isso significa que a tua voz tem peso real.

O jogo está vivo em **[sylora.lol](https://www.sylora.lol)**. Cria uma conta, explora o mundo, joga os capítulos disponíveis e diz-nos o que sentiste. Cada bug que encontras, cada sugestão que partilhas, cada momento que te surpreendeu — tudo isso molda o que Sylora vai ser.

**Como podes ajudar:**

- 🎮 **Joga** — explora tudo o que está disponível
- 🐛 **Reporta bugs** — abre uma [Issue](../../issues) com o que encontraste
- 💡 **Sugere** — tens uma ideia para o mundo ou para o jogo? Partilha nas Issues
- ⭐ **Dá uma estrela** — ajuda outros aventureiros a encontrar Sylora

> *Este é o início. O que construímos a seguir depende de quem aparece.*

---

<div align="center">

## 🛡 &nbsp; Segurança &nbsp; 🛡

</div>

A fortaleza que protege o reino:

- **SQL Injection** — prepared statements em todas as queries
- **Passwords** — bcrypt com `PASSWORD_DEFAULT`
- **Sessões** — tokens com rotação, revogação e cookies `HttpOnly + SameSite`
- **CSRF** — tokens validados em todos os formulários POST
- **Rate Limiting** — máx. 5 tentativas de login por 15 minutos
- **Headers** — CSP, X-Frame-Options, HSTS via `.htaccess`
- **Credenciais** — exclusivamente via variáveis de ambiente, nenhum segredo no código

---

<div align="center">

## ⚗ &nbsp; Stack Tecnológica &nbsp; ⚗

</div>

```
Backend        →  PHP 8.3 + MySQLi (prepared statements)
Base de Dados  →  MySQL 8.0
Servidor       →  Apache 2.4 (mod_rewrite, .htaccess)
Contentor      →  Docker  (php:8.3-apache)
Deploy         →  Railway
Email          →  Resend API / SMTP (PHPMailer)
```

---

<div align="center">

## 🕯 &nbsp; Instalação Local &nbsp; 🕯

</div>

**Pré-requisito:** [Docker](https://www.docker.com/) instalado.

```bash
# 1. Clona o repositório
git clone https://github.com/Blitzzx/sylora.git
cd sylora

# 2. Copia e preenche as variáveis de ambiente
cp .env.example .env

# 3. Sobe os containers
docker compose up -d

# 4. Importa o schema da base de dados
# Acede a http://localhost:8081 (phpMyAdmin) e importa sylora.sql
```

Aventura disponível em **http://localhost:8080** ✦

---

<div align="center">

<br/>

*Forjado com PHP, MySQL e um sopro de magia antiga.*

**✦ &nbsp; [sylora.lol](https://www.sylora.lol) &nbsp; ✦**

<br/>

</div>
