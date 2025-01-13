## Inicio da aplicação com o comando na pasta raiz:
    php artisan serve

## inicio do servidor temporario usando expose:
    expose share localhost (IP local):porta

## Ativar Listening do sistema de pagamento Stripe
    stripe listen --forward-to (Link gerado pelo Expose)/stripe/webhook

    com a execução do comando ele ira gerar um whasec_(ID) que será usado no arquivo .env