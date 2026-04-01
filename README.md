# 📦 Serenity — Sistema de Gerenciamento de Estoque

Sistema web de controle de estoque desenvolvido em PHP + MySQL, com autenticação, controle por perfil de acesso, gamificação de notificações e dashboard analítico.

---

## ✨ Funcionalidades

| Módulo | Descrição |
|---|---|
| 🔐 **Autenticação** | Login, registro, recuperação de senha, logout seguro |
| 📦 **Produtos** | CRUD completo com SKU, fornecedor, categoria, preço de custo/venda |
| 🏢 **Fornecedores** | Cadastro com CNPJ, contato e endereço |
| 🏷️ **Categorias** | Agrupamento de produtos |
| 📋 **Movimentações** | Entradas, saídas, ajustes, devoluções e perdas |
| 📊 **Inventário** | Visão consolidada do estoque atual |
| ⚠️ **Estoque Baixo** | Alertas visuais por produto com `min_quantity` individual |
| 📈 **Dashboard** | KPIs: valor de patrimônio (custo), receita potencial, produtos críticos |
| 👥 **Usuários** | Gerenciamento de contas (somente admin) |

---

## 🛠️ Stack Técnica

- **Backend:** PHP 8.1+ com PDO (prepared statements)
- **Banco:** MySQL 8.0+ / MariaDB 10.6+
- **Frontend:** HTML5 + CSS3 + JavaScript vanilla
- **Servidor:** Apache com `mod_rewrite` e `mod_headers`
- **Segurança:** CSRF tokens, bcrypt, rate limiting por sessão

---

## 🚀 Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/seu-usuario/serenity.git
cd serenity
```

### 2. Configure o ambiente

```bash
cp .env.example .env
```

Edite o `.env` com suas credenciais:

```env
DB_HOST=localhost
DB_NAME=serenity
DB_USER=root
DB_PASS=sua_senha_aqui
APP_ENV=development
```

> ⚠️ O arquivo `.env` está no `.gitignore` e **nunca** deve ser enviado ao repositório.

### 3. Crie o banco de dados

Execute o arquivo `schema.sql` (se existir) ou crie manualmente:

```bash
mysql -u root -p < schema.sql
```

Ou via phpMyAdmin: importe o arquivo `schema.sql`.

### 4. Configure o Apache

O `DocumentRoot` deve apontar para a pasta do projeto. Certifique-se de que:

```apache
AllowOverride All   # necessário para o .htaccess funcionar
```

Módulos necessários:
```bash
a2enmod rewrite headers expires deflate
```

### 5. Acesse no navegador

```
http://localhost/serenity/
```

**Conta padrão (criada pelo schema.sql):**
```
E-mail : admin@serenity.com
Senha  : admin123
```

> ⚠️ **Troque a senha do admin imediatamente após o primeiro acesso.**

---

## 📁 Estrutura do Projeto

```
serenity/
├── .env.example              # Template de variáveis de ambiente
├── .gitignore                # Protege .env e dados sensíveis
├── index.php                 # Roteador principal (front controller)
├── schema.sql                # Estrutura do banco de dados
│
├── app/
│   ├── config/
│   │   ├── config.php        # Constantes globais e configuração de erros
│   │   ├── database.php      # Conexão PDO (lê credenciais do .env)
│   │   ├── helpers.php       # Funções auxiliares e DataManager (legado JSON)
│   │   └── security.php      # CSRF, rate limiting, auditoria, permissões
│   │
│   ├── controllers/
│   │   ├── AuthController.php       # Login, registro, logout
│   │   ├── DashboardController.php  # KPIs e movimentações recentes
│   │   ├── ProductController.php    # CRUD de produtos
│   │   ├── SupplierController.php   # CRUD de fornecedores
│   │   ├── StockController.php      # Movimentações, inventário, estoque baixo
│   │   ├── CategoryController.php   # Listagem de categorias
│   │   └── UserController.php       # Gerenciamento de usuários (admin)
│   │
│   └── views/
│       ├── templates/
│       │   ├── header.php        # <head>, flash messages, script app.js (defer)
│       │   ├── footer.php        # Rodapé e fechamento do HTML
│       │   └── navigation.php    # Sidebar com link ativo dinâmico
│       ├── auth/                 # login, register, forgot-password
│       ├── dashboard/            # index do dashboard
│       ├── products/             # list, add, edit, view, categories
│       ├── suppliers/            # list, add, edit
│       ├── stock/                # movements, inventory, low-stock, adjustment
│       ├── admin/                # users
│       └── errors/               # 404
│
├── public/
│   ├── css/style.css         # Estilos globais
│   └── js/app.js             # JavaScript global
│
└── data/                     # Dados JSON legados (auditoria — migrar para SQL)
    └── audit/audit.json
```

---

## 🔒 Segurança

### Implementado

| Proteção | Como |
|---|---|
| **SQL Injection** | PDO com prepared statements em 100% das queries |
| **XSS** | `htmlspecialchars()` em todas as saídas de variáveis |
| **CSRF** | Token validado em **todos** os métodos POST |
| **Senhas** | bcrypt com custo 12 + rehash automático |
| **Rate Limiting** | Máx. 5 tentativas de login por sessão em 5 min |
| **Erros** | `display_errors` desativado em `APP_ENV=production` |
| **Credenciais** | Lidas do `.env`, nunca hardcoded no código |
| **Sessão** | `httponly`, `use_only_cookies` ativados |

### Checklist antes de ir para produção

```bash
# 1. Definir APP_ENV=production no .env
# 2. Trocar a senha do admin padrão
# 3. Ativar HTTPS e setar session.cookie_secure=1 em config.php
# 4. Remover ou proteger a pasta /data/
# 5. Confirmar que .env não está acessível publicamente
```

---

## 🌐 Rotas

O sistema usa um front controller simples via `?page=` e `?action=`.

| Rota | Controller | Método |
|---|---|---|
| `?page=login` | `AuthController::login()` | GET |
| `?page=login&action=authenticate` | `AuthController::authenticate()` | POST |
| `?page=register` | `AuthController::register()` | GET |
| `?page=register&action=doRegister` | `AuthController::doRegister()` | POST |
| `?page=forgot-password` | `AuthController::forgotPassword()` | GET |
| `?page=forgot-password&action=sendResetLink` | `AuthController::sendResetLink()` | POST |
| `?page=logout&action=logout` | `AuthController::logout()` | GET |
| `?page=dashboard` | `DashboardController::index()` | GET |
| `?page=products` | `ProductController::index()` | GET |
| `?page=products&action=add` | `ProductController::add()` | GET |
| `?page=products&action=save` | `ProductController::save()` | POST |
| `?page=products&action=edit&id=X` | `ProductController::edit()` | GET |
| `?page=products&action=update` | `ProductController::update()` | POST |
| `?page=products&action=delete&id=X` | `ProductController::delete()` | GET |
| `?page=suppliers` | `SupplierController::index()` | GET |
| `?page=stock&action=movements` | `StockController::movements()` | GET |
| `?page=stock&action=inventory` | `StockController::inventory()` | GET |
| `?page=stock&action=lowStock` | `StockController::lowStock()` | GET |
| `?page=stock&action=adjustment` | `StockController::adjustment()` | GET |
| `?page=stock&action=saveAdjustment` | `StockController::saveAdjustment()` | POST |
| `?page=categories` | `CategoryController::index()` | GET |
| `?page=users` | `UserController::index()` | GET (admin) |

---

## 🗄️ Banco de Dados

### Tabelas principais

| Tabela | Descrição |
|---|---|
| `users` | Contas de usuário (bcrypt, roles) |
| `products` | Produtos com SKU, preços, estoque mínimo |
| `categories` | Categorias de produtos |
| `suppliers` | Fornecedores |
| `stock_movements` | Histórico de entradas, saídas e ajustes |

### Regras de estoque

| Constante | Padrão | Significado |
|---|---|---|
| `LOW_STOCK_THRESHOLD` | 10 | Abaixo do `min_quantity` do produto → badge "Baixo" |
| `CRITICAL_STOCK_THRESHOLD` | 5 | Abaixo deste valor global → badge "Crítico" |

> O valor de patrimônio no Dashboard usa **preço de custo** (`cost_price × quantity`), seguindo o critério contábil correto.

---

## 🐛 Bugs Corrigidos (histórico)

| # | Arquivo | Descrição |
|---|---|---|
| 1 | `helpers.php` | `formatNumber()` declarada duas vezes → Fatal Error |
| 2 | `header.php` | `hamburger.addEventListener` em elemento `null` → TypeError |
| 3 | `ProductController::update()` | `supplier_id` ausente no `UPDATE products` |
| 4–9 | Todos os controllers | `Security::validateRequest()` ausente em métodos POST |
| 5 | `security.php` | `user_id` no auditLog era string `'guest'` em vez de `int 0` |
| 6a | `DashboardController` | Estoque crítico usava mesma query do baixo |
| 6b | `DashboardController` | Patrimônio calculado com `sale_price` em vez de `cost_price` |
| 7 | `products/list.php` | Badges de estoque ignoravam `min_quantity` individual |
| 8 | `auth/login.php` | Credenciais `admin@serenity.com / admin123` hardcoded no HTML |
| 9 | `auth/login.php` | Script `toggle-password` duplicado (conflito com `app.js`) |
| 10 | `config.php` | `display_errors=1` fixo, expunha erros em produção |
| 11 | `index.php` | Migração JSON→SQL incompleta, pasta `audit/` não garantida |
| 12 | `database.php` | Senha `Senai@118` hardcoded; erro PDO expunha detalhes de conexão |
| 13 | `footer.php` | `app.js` carregado duas vezes + `console.log`/`alert` de debug |
| 14 | `navigation.php` | Sidebar sem classe `active` na página atual |

---

## 🤝 Contribuindo

1. Fork o repositório
2. Crie uma branch: `git checkout -b fix/nome-do-bug`
3. Commit: `git commit -m "fix: descrição do que foi corrigido"`
4. Push e abra um Pull Request

### Padrão de commits

```
feat:     nova funcionalidade
fix:      correção de bug
security: correção de vulnerabilidade
docs:     documentação
refactor: refatoração sem nova feature
style:    formatação, sem lógica
```

---

## 📄 Licença

Marcos Gomes LICENSE
