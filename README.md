# Fithub — ltw16g07

## Projeto LTW 2025/26 · FEUP

Site de gestão de um ginásio privado. Membros podem navegar e inscrever-se em
aulas, ver perfis de treinadores, consultar a disponibilidade de equipamento e
deixar avaliações. Treinadores gerem as suas sessões e veem a lista de
inscritos. Administradores gerem o catálogo de aulas, equipamento, utilizadores
e disputas. Inclui sistema de lista de espera automática e notificações in-app.

### Como executar localmente

A partir da raiz do repositório:

```bash
php -S localhost:9000
```

Depois abrir [`http://localhost:9000`](http://localhost:9000) no navegador
(faz redirect para `pages/`).

### Inicializar / repor a base de dados

O ficheiro SQLite `database/database.db` já vem populado com dados de
demonstração. Para repor o estado inicial:

```bash
rm database/database.db
sqlite3 database/database.db < database/database.sql
```

> Em macOS/Linux o `sqlite3` está disponível por omissão; em Windows usar
> [https://sqlite.org/download.html](https://sqlite.org/download.html).

### Contas de demonstração (password: `password`)

| Tipo | Email |
| ------- | ------------------------------ |
| Admin | `admin@fithub.com` |
| Trainer | `ana@fithub.com` |
| Member | `jordan.miller@example.com` |

### Funcionalidades

**Members**
- Procurar aulas com filtros (tipo, treinador, dia, hora, localização)
- Inscrever/cancelar com verificação de capacidade transacional
- Lista de espera com **auto-promoção** quando há cancelamento + notificação
- Avaliar aulas (rating + comentário) — só permitido após inscrição
- Ver perfis de treinadores e disponibilidade de equipamento
- Editar perfil (nome, username, password, foto)
- Reservar sessões individuais com personal trainer
- Abrir disputas / cancelar membership

**Trainers**
- Editar bio, especialidade e certificações
- Criar/apagar sessões das suas aulas
- Ver roster (lista de membros inscritos) por sessão
- Aceder a analytics próprias

**Admins**
- CRUD de aulas (com sessões integradas no edit)
- CRUD de equipamento (com estado: operational / maintenance / out_of_order)
- CRUD de utilizadores (promover a trainer/admin, ativar/desativar, apagar)
- Resolver disputas
- Ver analytics globais

**Sistema transversal**
- Notificações in-app (sino no header com contagem por ler)
- API REST/JSON: `api/api_enroll.php`, `api/api_equipment.php`,
`api/api_schedule.php`, `api/api_trainers.php`
- AJAX progressivo: forms continuam a funcionar sem JavaScript

### Estrutura

```
├── actions/ # POST handlers (login, register, enrol, review, admin…)
├── api/ # Endpoints JSON / AJAX
├── css/ # Folha de estilos única (mobile-first, design tokens)
├── database/ # Schema SQL, ficheiro SQLite e classes PHP (PDO)
├── javascript/ # Scripts client-side (vanilla, sem frameworks)
├── partials/ # Páginas (controllers + partials)
├── templates/ # Funções drawX() para renderizar HTML
├── uploads/ # Fotografias de perfil carregadas pelos utilizadores
└── utils/ # Helpers (Session, CSRF)    
```

### Segurança

- Senhas com `password_hash` (bcrypt)
- **Prepared statements** em todas as queries (PDO)
- **CSRF tokens** em todos os formulários POST (validados com `hash_equals`)
- `htmlspecialchars` em todos os outputs dinâmicos
- Validação de MIME real em uploads de imagens
- Cookies de sessão com `HttpOnly` + `SameSite=Lax`
- Foreign keys ativas em SQLite (`PRAGMA foreign_keys = ON`)

### Tecnologias

HTML5 semântico · CSS3 (mobile-first, sem frameworks) · PHP 8 (PDO/SQLite)
· JavaScript vanilla + Ajax/JSON.

### Membros do Grupo

- Ariana —
- Madalena Rodrigues — up202404211
- Ricardo Estevéns — up202405321

















php -S localhost:8080
Depois abre no browser http://localhost:8080/index.php (http://localhost:8080/index.php)
