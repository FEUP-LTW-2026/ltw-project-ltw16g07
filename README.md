# FitHub — ltw16g07

## Projeto LTW 2025/26 · FEUP

Site de gestão de um ginásio privado. Membros podem navegar e inscrever-se em
aulas, ver perfis de treinadores, consultar a disponibilidade de equipamento e
deixar avaliações. Treinadores gerem as suas sessões e veem a lista de
inscritos. Administradores gerem o catálogo de aulas, equipamento e utilizadores. Inclui sistema de lista de espera automática e notificações in-app.

### Como executar (para o professor)

**1. Criar a base de dados**
```bash
php setup.php
```

**2. Iniciar o servidor**
```bash
php -S localhost:9000
```

**3. Abrir no browser**
[http://localhost:9000](http://localhost:9000)

### Contas de demonstração

| Tipo | Email | Password |
|------|-------|----------|
| Admin | `admin@fithub.com` | `password` |
| Trainer | `ana@fithub.com` | `password` |

Nota: Para o member é só fazer registo 


### Inicializar / repor a base de dados

O ficheiro SQLite `database/fithub.db` já vem populado com dados de
demonstração. Para repor o estado inicial:

```bash
rm database/database.db
sqlite3 database/database.db < database/database.sql
```

> Em macOS/Linux o `sqlite3` está disponível por omissão; em Windows usar
> [https://sqlite.org/download.html](https://sqlite.org/download.html).



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
- API REST/JSON: `api/index.php`, `api/equipment.php`,
`api/schedule.php`, `api/trainers.php`
- AJAX progressivo: forms continuam a funcionar sem JavaScript

### Estrutura

```
├── actions/         # POST handlers (login, register, book_class, logout, add_review…)
├── api/             # Endpoints JSON / AJAX (equipment.php, trainers.php, schedule.php, index.php)
├── css/             # Folhas de estilo (style.css + pages específicas)
├── database/        # Ficheiro SQLite local (database/fithub.db) 
├── sql              # Schema + seed
├── javascript/      # Scripts client-side (vanilla JS)
├── templates/       # Templates .tpl.php (funções draw_*/render_* para apresentação)
├── pages/           # Controladores / páginas PHP que preparam dados e chamam templates
├── includes/        # Helpers (auth.php)
├── config/          # Configuração (database.php)
└── uploads/         # Imagens enviadas (trainers, gym)

```

### Segurança

- Senhas com password_hash() (bcrypt).
- Prepared statements (PDO) em todas as queries.
- CSRF tokens em formulários POST (csrf_input/validate_csrf, comparação com hash_equals).
- Escapa todos os outputs com htmlspecialchars / helper h().
- Validação de MIME e extensão em uploads de imagem; armazenamento em uploads/ com nomes seguros.
- Sessões com cookies HttpOnly e SameSite=Lax (configurar em session_start / envio de cookies).
- SQLite: PRAGMA foreign_keys = ON ativado nas ligações.
- Não commitar ficheiros de base de dados locais — adicionar ao .gitignore (ex.: /database/*.db, *.db, *.sqlite*).


### Tecnologias

HTML5 semântico · CSS3 (mobile-first, sem frameworks) · PHP 8 (PDO/SQLite)
· JavaScript vanilla + Ajax/JSON.

### Membros do Grupo

- Ariana —
- Madalena Rodrigues — up202404211
- Ricardo Estevéns — up202405321

















php -S localhost:8080
Depois abre no browser http://localhost:8080/index.php (http://localhost:8080/index.php)
