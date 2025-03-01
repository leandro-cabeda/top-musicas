# Desafio Projeto Top 5 Tião Carreiro

Este projeto é uma página web que exibe as 5 músicas mais tocadas da dupla Tião Carreiro e Pardinho, permitindo que os usuários possam sugirir 
novas músicas via link do YouTube.

## Tecnologias Usadas

- Backend: Laravel 11 e PHP 8.2
- Frontend: ReactJS 19. com Next.js 15.
- Banco de Dados: PostgreSQL
- Conteinerização: Docker
- Autenticação: JWT

## Como Rodar o Projeto Localmente

### 1. Clonar o Repositório

Clone o repositório para sua máquina local:

```bash
git clone https://github.com/leandro-cabeda/top-musicas.git


## 2. Para começar configurar e instalar o projeto segue os passos.

### 2.1. Precisa ter o composer instalado e nisso executa os comando na pasta do backend
- composer install
- composer update (Se necessário atualizar laravel)
- composer require laravel/sanctum (Para autenticação)
- php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider" (Cria a configuração)
- php artisan migrate (Rodar as migrações das tabelas do banco)
- php artisan db:seed (Rodar todos os seedrs disponivel)
- php artisan serve (Subir aplicação do backend na porta 8000)
- configuração do banco de dados postgresql se encontra no arquivo ".env"

#### 2.1.1. Problema de requisição por causa do CSRF no backend
Caso tiver problema de requisição ao acessar as rotas por causa do CSRF, pode ser feito a seguinte coisa:
- Entrar nesse path do arquivo do projeto: ./vendor/laravel/framework/src/Illuminate/Foundation/Configuration/Middleware.php
- Nisso, vai comentar essa linha onde contém isso:  \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
- Na função:  getMiddlewareGroups()  => e com isso irá funcionar para testar pelo menos.

### 2.2. Passos para instalação e configuração da pasta frontend.
- npm i --force (instalação das bibliotecas)
- npm run build (para buildar o projeto)
- npm start (para subir aplicação do frontend na porta 3000)

## Executar o Docker container com as configurações do arquivo docker-composer.yml
- docker-compose up --build -d (Executa primeiro)
- docker exec -it laravel_backend bash (Executa segundo)
- php artisan migrate (Executa esse dentro do bash para rodar as migrations no docker)
- docker-compose exec backend php artisan config:clear (Limpar)
- docker-compose exec backend php artisan cache:clear (Limpar)
- docker-compose exec backend php artisan swagger-lume:generate (Gerar a documentação do swagger e ficar disponivel no laravel)


## URl Endpoint Swagger da Api documentação
- http://127.0.0.1:8000/api/documentation
- http://127.0.0.1:8000/docs



