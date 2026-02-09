🎬 Movies Backend 

Este projeto foi desenvolvido como parte do Teste Back-end – Parte 2, com foco na integração via backend com uma API externa e disponibilização de dados paginados para consumo em interface de grid.

🎯 Objetivo 

Listar filmes em grid, contendo:

Título

Capa (imagem)

Ano

Utilizar o endpoint /titles da API MoviesDatabase (RapidAPI)

Implementar paginação

Garantir que a integração com a API externa seja feita exclusivamente no backend

🛠️ Tecnologias Utilizadas

Laravel 11

PHP 8.2

HTTP Client do Laravel

Blade (para demo visual)

Bootstrap 5 (CDN)

RapidAPI – MoviesDatabase

🧱 Arquitetura da Solução

A aplicação foi estruturada seguindo boas práticas de separação de responsabilidades:

Controller

Responsável por receber requisições HTTP e devolver respostas JSON

Service (MovieApiService)

Responsável por integrar com a API externa (RapidAPI)

Centraliza headers, timeout, tratamento de erros e mapeamento de dados

Configuração segura

Credenciais da RapidAPI armazenadas em variáveis de ambiente (.env)

Frontend de demonstração

Implementado apenas para demonstrar o funcionamento do grid e da paginação

Consome exclusivamente o endpoint interno /api/titles

⚠️ Em nenhum momento o frontend acessa diretamente a RapidAPI.

🔌 Integração com a RapidAPI

Endpoint utilizado:

GET /titles


Headers obrigatórios:

X-RapidAPI-Key

X-RapidAPI-Host

Esses headers não ficam expostos no frontend e são utilizados apenas no backend.

Variáveis de ambiente (.env)
RAPIDAPI_KEY=YOUR_RAPIDAPI_KEY
RAPIDAPI_HOST=moviesdatabase.p.rapidapi.com
RAPIDAPI_BASE_URL=https://moviesdatabase.p.rapidapi.com
RAPIDAPI_TIMEOUT=10

📄 Endpoint Backend Disponível
Listar títulos (com paginação)
GET /api/titles?page=1

Exemplo de resposta:
{
  "page": 1,
  "nextPage": 2,
  "hasMore": true,
  "total": 10,
  "count": 9,
  "items": [
    {
      "id": "tt0000081",
      "title": "Les haleurs de bateaux",
      "year": 1896,
      "poster": "https://m.media-amazon.com/..."
    }
  ]
}

Campos retornados:

page → página atual

nextPage → próxima página (quando disponível)

hasMore → indica se há mais páginas

total → total informado pela API externa (quando disponível)

count → quantidade real de itens retornados nesta página

items → lista de filmes para o grid

🔁 Paginação

A API externa nem sempre fornece metadados claros de paginação.
Por isso, foi implementado um fallback inteligente:

Quando não existe nextPage explícito, mas a API retorna itens, assume-se page + 1

Quando uma página retorna zero itens, a paginação é interrompida

Isso garante uma experiência de navegação consistente no frontend.

🖼️ Tratamento de Imagens

Algumas imagens retornadas pela API (hospedadas em m.media-amazon.com) podem não ser exibidas devido a políticas de hotlink / CORS do provedor externo.

Solução aplicada

Implementado fallback visual diretamente na tag <img>

Caso a imagem falhe, é exibido um placeholder inline (SVG via data URI)

Essa abordagem:

Não depende de serviços externos

Funciona offline

Evita problemas de bloqueio por rede, CORS ou adblock

🧪 Demo Visual (Grid)

Para demonstrar o funcionamento do backend, foi criada uma view simples em Blade:

GET /movies


Essa tela:

Consome exclusivamente /api/titles

Exibe os filmes em grid (Bootstrap)

Possui paginação (Anterior / Próxima / Ir para página)

Implementa loading, mensagens de erro e fallback de imagem

⚠️ A view existe apenas para demonstração técnica do teste.

▶️ Como executar o projeto

Clonar o repositório

Instalar dependências:

composer install


Configurar o .env com as credenciais da RapidAPI

Subir o servidor (exemplo):

php -S 127.0.0.1:8000 -t public


Acessar:

API: http://127.0.0.1:8000/api/titles?page=1

Demo Grid: http://127.0.0.1:8000/movies

✅ Conclusão

O objetivo do Teste Back-end – Parte 2 foi integralmente atendido:

✔️ Integração via backend com API externa

✔️ Paginação implementada

✔️ Dados preparados para grid (título, capa e ano)

✔️ Segurança das credenciais

✔️ Tratamento de falhas de imagem

✔️ Demonstração funcional do resultado
