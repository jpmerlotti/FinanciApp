---
trigger: always_on
---

## 1. Identidade e Filosofia
* Projeto: FinanciApp - SaaS de Gestão Financeira "Ultra Simples".
* Persona do Dev: Full Stack Master (PHP/Laravel). Foco em performance, Clean Code, UX sem fricção e redução de dependências externas.
* Filosofia de UX: "Contexto Único" (Single Resource View). O usuário deve conseguir fazer tudo (ver, editar, analisar) sem sair da listagem principal. Estilo "Notion-like" para edição de tabelas.
## 2. Tech Stack (Bleeding Edge)
* Linguagem: PHP 8.5 (Strict typing).
* Framework: Laravel 12.x.
* Admin/UI: FilamentPHP v4 (TALL Stack).
* Front-end: AlpineJS v3 (Nativo), TailwindCSS v3/v4.
* Banco de Dados: PostgreSQL.
* Ambiente: Laravel Sail (Docker).
## 3. Arquitetura de Dados & Business Logic
### Multi-tenancy
- Modelo: Single Database, Row-level filtering.
- Tenant: Model Organization.
- User: Implementa HasTenants. Relacionamento belongsToMany com Organizations.
- Regra: Todo Model (exceto User) deve ter organization_id. Scopes globais são aplicados automaticamente pelo Filament.
### Transações (Core)
- Money Pattern: Valores monetários salvos sempre como integer (centavos) no banco (amount_cents). Front-end converte visualmente.
- Recorrência Inteligente:
  - Não usamos Cron Jobs para criar parcelas futuras.
  - Usamos Batch Insert no momento da criação da transação pai.
  - Agrupamento via UUID recurring_group_id.
  - Lógica de data: Carbon::addMonthsNoOverflow para evitar erros em dias 31.
- Tags: Relacionamento N-N isolado por Tenant. Possuem atributo color (Hex) e slug.
## 4. Padrões de Interface (UI/UX)
### Edição Inline (Tabela)
- Abordagem: Evitar ViewColumn complexas. Preferir TextInputColumn estendida.
- Data: Componente customizado DateInputColumn que estende TextInputColumn.
  - Usa AlpineJS puro para interagir com $wire.updateTableColumnState.
  - Corrige propagação de cliques (x-on:click.stop) para permitir abertura do calendário nativo.
- Status: SelectColumn com injeção de classes CSS dinâmicas para cores (Badges simulados).
- Estilo Notion: Classes utilitárias que removem bordas e background de inputs (!border-0 !shadow-none !bg-transparent).
### Dashboards & Widgets
- Reatividade: Widgets usam InteractsWithPageTable para respeitar filtros e abas da listagem.
- Visual:
  - FinancialHealthOverview: Widget customizado (Blade puro) com barras de progresso empilhadas (Recebido vs Pendente).
  - Lógica de Balanço Real: Calcula apenas o que está com status paid.
## 5. Diretrizes de Desenvolvimento (Rules)
1. Zero Plugins Desnecessários: Não instalar plugins de terceiros para funcionalidades triviais (ex: resize de coluna, botões simples). Implementar nativo via Blade/Alpine.
2. AlpineJS Leve: Em componentes customizados, não depender de x-load ou assets internos do Filament que podem mudar de nome. Escrever a lógica JS inline quando simples.
3. Segurança de Salvamento: Colunas editáveis (TextInputColumn) DEVEM ter regras de validação (->rules(['date', 'numeric'])) e estar no $fillable do Model.
4. Tratamento de Erros: Logs explícitos (Log::info) ao criar lógicas complexas de update manual.
## 6. Estrutura de Arquivos Chave (Onde as coisas estão)
- app/Filament/Resources/TransactionResource.php: Definição principal da tabela e form.
- app/Filament/Resources/TransactionResource/Pages/CreateTransaction.php: Lógica de recorrência (Batch Insert) e interceptação de dados (mutateFormDataBeforeCreate).
- app/Filament/Tables/Columns/DateInputColumn.php: Componente customizado para data inline.
- app/Filament/Resources/TransactionResource/Widgets/FinancialHealthOverview.php: Gráficos de progresso financeiro.
- app/Models/Transaction.php: Model principal com Casts e Fillables atualizados.