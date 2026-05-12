<div align="center">

```
███████╗██╗   ██╗██╗      ██████╗ ██████╗  █████╗
██╔════╝╚██╗ ██╔╝██║     ██╔═══██╗██╔══██╗██╔══██╗
███████╗ ╚████╔╝ ██║     ██║   ██║██████╔╝███████║
╚════██║  ╚██╔╝  ██║     ██║   ██║██╔══██╗██╔══██║
███████║   ██║   ███████╗╚██████╔╝██║  ██║██║  ██║
╚══════╝   ╚═╝   ╚══════╝ ╚═════╝ ╚═╝  ╚═╝╚═╝  ╚═╝
```

### *Uma aventura além dos mapas conhecidos*

![PHP](https://img.shields.io/badge/PHP-8.3-7C3AED?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4F46E5?style=for-the-badge&logo=mysql&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-2.4-6D28D9?style=for-the-badge&logo=apache&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-7C3AED?style=for-the-badge&logo=docker&logoColor=white)
![Railway](https://img.shields.io/badge/Deploy-Railway-4F46E5?style=for-the-badge&logo=railway&logoColor=white)

</div>

---

## ✦ O Mundo de Sylora

Sylora é um **RPG de aventura baseado na web** onde o jogador explora um arquipélago misterioso cheio de segredos, criaturas e histórias esquecidas. Cada ilha guarda um capítulo diferente — e nenhum herói chega ao fim igual ao que partiu.

> *"Os mapas mostram terras. Sylora mostra destinos."*

---

## ⚔️ Funcionalidades

| Módulo | Descrição |
|--------|-----------|
| 🗺️ **Mapa das Ilhas** | Navegação visual pelo arquipélago com áreas desbloqueáveis |
| ⚔️ **Sistema de Jogo** | Progressão por capítulos com HP, XP, dano e saves por slot |
| 📖 **História** | Narrativa ramificada com `story_progress` persistente |
| 👤 **Perfil** | Avatar personalizado, bio, e página pública de utilizador |
| 🤝 **Amizades** | Sistema de pedidos, aceitação e bloqueio entre jogadores |
| 💬 **Comentários** | Mural de comentários nos perfis de outros jogadores |
| 🔐 **Autenticação** | Login seguro com "lembrar-me" de 30 dias e proteção CSRF |
| 🔍 **Pesquisa** | Encontra outros aventureiros pelo nome |

---

## 🛡️ Segurança

- Prepared statements em todas as queries (proteção SQL injection)
- Passwords com bcrypt (`PASSWORD_DEFAULT`)
- Tokens de sessão com rotação e revogação
- Rate limiting no login (5 tentativas / 15 min)
- Cookies `HttpOnly`, `SameSite=Lax` e `Secure` em HTTPS
- Headers de segurança via `.htaccess` (CSP, X-Frame-Options, etc.)
- Variáveis de ambiente para credenciais — nenhum segredo no código

---

## 🧰 Stack Tecnológica

```
Backend   →  PHP 8.3 + MySQLi (prepared statements)
Base de Dados  →  MySQL 8.0
Servidor  →  Apache 2.4 (.htaccess, mod_rewrite)
Container →  Docker (php:8.3-apache)
Deploy    →  Railway
```

---

## 🚀 Instalação Local

### Pré-requisitos
- [Docker](https://www.docker.com/) instalado

### Passos

```bash
# 1. Clona o repositório
git clone https://github.com/Blitzzx/sylora.git
cd sylora

# 2. Copia e preenche as variáveis de ambiente
cp .env.example .env

# 3. Sobe os containers
docker compose up -d

# 4. Importa o schema da base de dados
# Acede a http://localhost:8081 (phpMyAdmin) e importa o ficheiro sylora_db.sql
```

A aplicação fica disponível em **http://localhost:8080**

---

## ☁️ Deploy no Railway

1. Faz fork / push para o teu GitHub
2. Cria um novo projeto no [Railway](https://railway.app) → *Deploy from GitHub*
3. Adiciona o plugin **MySQL**
4. Em **Settings → Variables**, define:

```env
DB_HOST=      # Internal Host do plugin MySQL
DB_USER=      # MYSQLUSER
DB_PASS=      # MYSQLPASSWORD
DB_NAME=      # MYSQLDATABASE
SITE_URL=     # https://o-teu-projeto.up.railway.app
APP_ENV=      production
```

5. Vai ao plugin MySQL → **Query** e importa o `sylora_db.sql`

O Railway faz o build automaticamente com o `Dockerfile` incluído.

---

## 📁 Estrutura do Projeto

```
sylora/
├── api/                  # Endpoints JSON (amizades, comentários, saves)
├── assets/               # Imagens, ícones e media
├── css/                  # Estilos
├── js/                   # Scripts do lado do cliente
├── includes/
│   ├── config.php        # Sessões, constantes, ambiente
│   ├── db.php            # Ligação à base de dados
│   ├── auth.php          # Autenticação e tokens
│   ├── functions.php     # Helpers (sanitize, validação, redirect)
│   ├── header.php        # Navegação e UI
│   └── footer.php        # Footer e scripts
├── index.php             # Homepage + mapa
├── login.php             # Autenticação
├── register.php          # Registo
├── profile.php           # Edição de perfil
├── u.php                 # Perfil público
├── jogar.php             # Motor de jogo
├── historia.php          # Narrativa
├── search.php            # Pesquisa de jogadores
├── avatar.php            # Servidor de avatares
├── sylora_db.sql         # Schema da base de dados
├── Dockerfile            # Imagem PHP 8.3 + Apache
└── .env.example          # Template de variáveis de ambiente
```

---

## 🗺️ Roadmap

- [ ] Sistema de combate em tempo real
- [ ] Inventário e itens
- [ ] Guilds / Clãs
- [ ] Conquistas e troféus
- [ ] Modo história cooperativo
- [ ] API pública para extensões

---

<div align="center">

*Forjado com PHP, MySQL e um toque de magia* ✦

</div>
