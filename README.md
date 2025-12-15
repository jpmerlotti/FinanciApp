# 💰 FinanciApp - Gestão Financeira Inteligente (TALL Stack)

![PHP Version](https://img.shields.io/badge/php-%5E8.4-777BB4?style=flat&logo=php)
![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel)
![Filament](https://img.shields.io/badge/FilamentPHP-v4-F2C94C?style=flat&logo=filament)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=flat&logo=tailwindcss)

> Uma aplicação de controle financeiro "Ultra Simples" focada em eficiência de UX, performance de banco de dados e arquitetura limpa usando o ecossistema TALL Stack.

## 📸 Screenshots

_(Coloque aqui um print da sua tela de listagem com os Widgets e Abas)_

## 🚀 Sobre o Projeto

O objetivo deste projeto é ser uma ferramenta de **fricção zero** para o controle de fluxo de caixa pessoal. A arquitetura foi desenhada priorizando a performance e a experiência do usuário (UX), eliminando cliques desnecessários e dashboards segregados. Tudo acontece em um **Contexto Único** (Single Resource View).

### Principais Diferenciais Técnicos

-   **Arquitetura Reativa:** Widgets de estatísticas que interagem dinamicamente com a tabela. Ao filtrar por mês ou ano (Abas), os cards de Balanço, Receita e Despesa recalculam instantaneamente sem refresh completo (`ExposesTableToWidgets`).
-   **Performance Financeira:** Armazenamento de valores monetários em **Inteiros (cents)** no banco de dados para evitar erros de ponto flutuante, com formatação automática no Front-end.
-   **Input Mask ATM Style:** Implementação de máscara de moeda via **AlpineJS puro** (`RawJs`), sem dependências pesadas de terceiros, garantindo leveza no carregamento.
-   **Smart Recurring Engine:** Sistema de recorrência que utiliza **Batch Inserts** para criar projeções futuras (mensal, semanal, anual) em uma única query SQL, com lógica de datas robusta (`Carbon::addMonthsNoOverflow`) para lidar com meses de tamanhos variados.

## 🛠 Tech Stack

-   **Back-end:** PHP 8.4+, Laravel 12
-   **Admin/UI:** FilamentPHP v4
-   **Front-end:** Blade, AlpineJS, TailwindCSS
-   **Database:** PostgreSQL compatible with MySQL
-   **Infra:** Docker (Laravel Sail)

## ⚡ Funcionalidades e Decisões de Design

### 1. Gestão de Transações

O formulário de criação utiliza `Groups` e `Grids` contextuais. A opção de recorrência só aparece se solicitada, evitando poluição visual (**Progressive Disclosure**).

### 2. Visão Macro vs. Micro

O Header do painel exibe duas linhas de métricas simultâneas:

-   **Linha 1 (Macro):** Balanço Geral do Ano (Fixo).
-   **Linha 2 (Micro):** Balanço do Período Selecionado (Dinâmico conforme a Aba ativa).

### 3. Recorrência Otimizada

Ao criar uma despesa recorrente (ex: Netflix), o sistema não usa _Cron Jobs_. Ele projeta os lançamentos futuros fisicamente no banco, vinculados por um `recurring_group_id`.

-   **Vantagem:** Permite edição individual de parcelas futuras e visualização real do fluxo de caixa futuro.

## ⚙️ Instalação e Execução

Este projeto utiliza **Laravel Sail** para um ambiente de desenvolvimento padronizado.

1. **Clone o repositório:**

```bash
git clone [https://github.com/jpmerlotti/FinanciApp.git](https://github.com/jpmerlotti/FinanciApp.git)
cd FinanciApp
```

2. **Configure o ambiente:**

```Bash
cp .env.example .env

# Ajuste as credenciais de banco no .env se necessário
```

3. **Suba os containers (Docker):**

```Bash
./vendor/bin/sail up -d
```

4. **Instale as dependências e gere a key:**

```Bash
./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate
```

5. **Execute as Migrations:**

```Bash
./vendor/bin/sail artisan migrate
```

6. **Crie um usuário administrativo:**

```Bash
./vendor/bin/sail artisan make:filament-user
```

7. **Acesse em: http://localhost/app**

## 🧪 Padrões de Código (Snippets)

Máscara JS Pura (Sem dependências npm)
Utilização de RawJs do Filament para injetar lógica nativa no input:

```PHP
TextInput::make('amount_cents')
    ->mask(RawJs::make(<<<'JS'
        let value = $el.value.replace(/\D/g, '');
        $el.value = (Number(value) / 100).toLocaleString('pt-BR', {
        minimumFractionDigits: 2
        });
    JS))
```

## Batch Insert com Carbon

Lógica para garantir que recorrências no dia 31 não quebrem em Fevereiro:

```PHP
match ($interval) {
    'monthly' => $date->addMonthsNoOverflow($i),
    'annually' => $date->addYears($i),
    // ...
};
```

## 📝 Licença

Este projeto está sob a [licença MIT](LICENSE).
