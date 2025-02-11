<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticado com Sucesso</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="text-center">
        <h2>Autenticação Concluída ✅</h2>
        <p>Agora você está logado com sua conta Microsoft.</p>
        
        <form action="{{ route('logout') }}" method="GET">
            @csrf
            <button type="submit" class="btn btn-danger mt-3">Sair da Conta</button>
        </form>
    </div>
</body>
</html>
